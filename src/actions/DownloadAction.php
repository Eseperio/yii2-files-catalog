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

/**
 * Downloads a file. If inline is set to true, file will be displayed in browser
 * @property DefaultController|Controller|\yii\rest\Controller $controller
 */
class DownloadAction extends Action
{
    use ModuleAwareTrait;

    /**
     *
     * @param $uuid
     * @param $inline
     * @return void
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\RangeNotSatisfiableHttpException
     */
    public function run($uuid,$inline= false)
    {
        $model = $this->controller->findModel($uuid, Inode::class);
        $stream = $model->getFile();
        $attachmentName = $model->publicName . "." . $model->extension;
        $options =[];
        if($inline){
            $options['inline'] = true;
            $options['mimeType'] = $model->mime;
        }
        Yii::$app->response->sendContentAsFile($stream, $attachmentName,$options);
    }
}
