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
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        $activeMimeTypes = [
            'text/html', 'application/xhtml+xml', 'image/svg+xml',
            'application/xml', 'text/xml', 'application/javascript', 'text/javascript'
        ];
        $mimeType = strtolower(trim(explode(';', (string)$model->mime, 2)[0]));
        if ($inline && in_array($mimeType, $activeMimeTypes, true)) {
            // Never serve user supplied active content in the application origin.
            $options['mimeType'] = 'application/octet-stream';
        } elseif($inline){
            $options['inline'] = true;
            $options['mimeType'] = $model->mime;
        }
        Yii::$app->response->sendContentAsFile($stream, $attachmentName,$options);
    }
}
