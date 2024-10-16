<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\Controller;

class DownloadAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run($uuid,$inline= false)
    {
        $model = $this->controller->findModel($uuid, Inode::class);
        $stream = $model->getFile();
        $attachmentName = $model->publicName . "." . $model->extension;
        $options =[];
        if($inline){
            $options['inline'] = true;
            $options['mime'] = $model->mime;
        }
        Yii::$app->response->sendContentAsFile($stream, $attachmentName,$options);
    }
}
