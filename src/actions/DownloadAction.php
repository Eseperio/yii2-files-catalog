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

    public function run()
    {
        $model = $this->controller->findModel(Yii::$app->request->get('uuid'), Inode::class);

        $stream = $model->getStream();
        $attachmentName = $model->publicName . "." . $model->extension;
        Yii::$app->response->sendStreamAsFile($stream, $attachmentName, [
            'fileSize' => $model->getSize()
        ]);
    }
}
