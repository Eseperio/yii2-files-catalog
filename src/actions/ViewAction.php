<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\Controller;

class ViewAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {
        $model = $this->controller->findModel(Yii::$app->request->get('uuid', false));

        /* @todo: Check ACL */

        $allowedMimes = $this->module->browserInlineMimeTypes;
        $tag = false;
        if (array_key_exists($model->mime, $allowedMimes)) {
            $tagName = $allowedMimes[$model->mime];
            switch ($tagName) {
                case 'video':
                    $tag= Html::tag('video',Html::tag('source','',[
                        'src'=>"$model->"
                    ]),['controls']);
                    break;
                case 'audio':
                    break;
                case 'img':
                    break;
            }
        }


        return $this->controller->render('view', [
            'model' => $model,
            'tag' => $tag,
            'checkFilesIntegrity' => $this->module->checkFilesIntegrity
        ]);

    }
}
