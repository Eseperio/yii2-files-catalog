<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use dosamigos\fileupload\FileUpload;
use dosamigos\fileupload\FileUploadAsset;
use dosamigos\fileupload\FileUploadPlusAsset;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\validators\FileValidator;
use yii\web\JsExpression;

/**
 * Class Uploader
 * @package eseperio\filescatalog\widgets
 */
class Uploader extends FileUpload
{

    use ModuleAwareTrait;

    /**
     * @inheritdoc
     */
    public $useDefaultButton = true;
    /**
     * @var Inode
     */
    public $model;
    /**
     * @var null
     */
    public $targetUuid = null;
    /**
     * @var string id of pjax container to be refreshed when load finished
     */
    public $pjaxId;
    /**
     * @var string jQuery selector for the container where upload errors must be displayed
     */
    public $errorsContainerSelector = "#filex-errors";
    /**
     * @var string jQuery selector for the progress bar where display progress.
     */
    public $progressBarSelector = "#filex-progress";
    /**
     * @var string markup for displaying errors. {error} is a placeholder that will be replaced with real error message.
     */
    public $errorTemplate = '<div class="alert alert-danger">{error}</div>';
    /**
     * @var int time in milliseconds that erros will be displayed
     */
    public $errorDuration = 3000;

    /**
     * @var string css selector of area to be enabled as dropzone. Defaults to null, which means all document will be a dropzone
     */
    public $dropZone = null;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {

        $this->initTargetDir();

        if (empty($this->model))
            $this->model = \Yii::createObject([
                'class' => Inode::class
            ]);

        $this->attribute = 'file';
        $this->url = ['/filex/default/upload'];
        $this->options['multiple'] = true;

        $fileValidator = new FileValidator();
        $this->clientOptions = [
            'maxFileSize' => $fileValidator->getSizeLimit(),
            'formData' => [
                'target' => $this->targetUuid,
            ]
        ];
        if (!empty($this->dropZone)) {
            $this->clientOptions['dropZone'] = $this->dropZone;
        }

        $this->registerEvents();
        parent::init();
    }

    private function initTargetDir()
    {
        if (empty($this->targetUuid) && empty($this->model))
            throw new InvalidConfigException(__CLASS__ . "::targetUuid or model must be defined");

        if (!empty($this->model)) {
            $this->targetUuid = $this->model->uuid;
        }

    }

    /**
     *
     */
    public function registerEvents()
    {

        $this->view->registerJsVar('FILEX_ERRORS', []);
        $errorMessage = Yii::t('filescatalog', 'An error ocurred while uploading the file/s');

        $pjaxSnippet = '';
        if (!empty($this->pjaxId) && $this->module->usePjax) {
            $url = \Yii::$app->request->url;
            $pjaxSnippet = "
            if(FILEX_ERRORS.length<=0){
                $.pjax.reload({
                    container: '#{$this->pjaxId}',
                    url: '$url',
                    push: false,
                    replace: false
                });
            }else{
                FILEX_ERRORS=[];
            }";
        }

        $this->clientEvents = [
            'fileuploaddone' => new JsExpression(<<<JS
            function (e, data) {
            $.each(data.result.files, function (index, file) {
                if(file.errors){
                    $("#{$this->id}-errors").append($('<p>',file.name))
                    $.each(file.errors,(k,v)=>{
                        FILEX_ERRORS.push(v)
                        $("{$this->errorsContainerSelector}").append($('<p>',{
                            text: (Array.isArray(v)?v[0]:v),
                            class:'text-danger'
                        }))
                    })
                }
            });
            }
JS
            ),
            'fileuploadstop' => new JsExpression(<<<JS
function(e,data){
    {$pjaxSnippet}
}

JS
            ),
            'fileuploadprogressall' => new JsExpression(<<<JS
          function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('{$this->progressBarSelector} .progress-bar').css(
            'width',
            progress + '%'
        );
        if(progress>=100){
            console.log(progress);
            $('{$this->progressBarSelector}').hide();

        }
    }
JS
            ),
            'fileuploaddrop' => new JsExpression(<<<JS
          function (e, data) {
    e.stopPropagation();
    }
JS
            ),
            'fileuploadstart' => new JsExpression(<<<JS
          function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('{$this->progressBarSelector}').show();
    }
JS
            ),
            'fileuploadfail' => new JsExpression(<<<JS
function (e, data) {
    if(data.errorThrown ==="Found"){
        return;
    }
    let message = '{$this->errorTemplate}'.replace('{error}',data.files[0].name + ": "+ data.errorThrown)
    message= $($.parseHTML(message)[0]);
    setTimeout(()=>{
        message.remove();
    },{$this->errorDuration})
    FILEX_ERRORS.push(data.errorThrown);
    $('{$this->errorsContainerSelector}').append(message).show();
}
JS
            )
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $input = $this->hasModel()
            ? Html::activeFileInput($this->model, $this->attribute, $this->options)
            : Html::fileInput($this->name, $this->value, $this->options);

        echo $this->render($this->uploadButtonTemplateView, [
            'input' => $input,
            'id' => $this->id,
            'isVersion' => isset($this->model->uuid) && $this->module->allowVersioning,
            'addFilesIconClass' => $this->module->addFilesIconClass,
            'showLabels' => $this->module->showBreadcrumbButtonLabels,

        ]);

        $this->registerClientScript();
    }

    /**
     * Registers required script for the plugin to work as jQuery File Uploader
     */
    public function registerClientScript()
    {
        $view = $this->getView();

        if ($this->plus) {
            FileUploadPlusAsset::register($view);
        } else {
            FileUploadAsset::register($view);
        }

        $options = Json::encode($this->clientOptions);
        $id = $this->options['id'];

        $js[] = ";$(document).off('drop');jQuery('#$id').fileupload($options);";
        if (!empty($this->clientEvents)) {
            foreach ($this->clientEvents as $event => $handler) {
                $js[] = "jQuery('#$id').on('$event', $handler);";
            }
        }
        $view->registerJs(implode("\n", $js));
    }
}
