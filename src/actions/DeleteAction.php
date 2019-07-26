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
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\Controller;

class DeleteAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {
        $model = $this->controller->findModel(Yii::$app->request->get('uuid'), File::class);
        $versions = $model->versions;

        if (!empty($versions) && is_array($versions) && !Yii::$app->request->get('original', false))
            $model = end($versions);

        $parentUuid = $model->getParent()->select('uuid')->scalar();




    }

    /**
     * @param $tagName
     * @param string $base64data
     * @param $model
     * @return string
     */
    private function getTag($tagName, string $base64data, $model): string
    {
        switch ($tagName) {
            case 'video':
            case 'audio':
                $tag = $this->getAudioVideoTag($tagName, $base64data, $model);
                break;
            case 'img':
                $tag = $this->getImgTag($base64data);
                break;
            case 'iframe':
                $tag = $this->getIframeTag($base64data);
                break;
        }

        return $tag;
    }

    /**
     * @param $tagName
     * @param string $base64data
     * @param $model
     * @return string
     */
    private function getAudioVideoTag($tagName, string $base64data, $model): string
    {
        $tag = Html::tag($tagName, Html::tag('source', '', [
            'src' => $base64data,
            'type' => $model->mime
        ]), ['controls' => 1]);

        return $tag;
    }

    /**
     * @param string $base64data
     * @return string
     */
    private function getImgTag(string $base64data): string
    {
        $tag = Html::img($base64data, [
            'class' => 'img-responsive'
        ]);

        return $tag;
    }

    /**
     * @param string $base64data
     * @return string
     */
    private function getIframeTag(string $base64data): string
    {
        $tag = Html::tag('iframe', '', [
            'src' => $base64data,
            'style' => [
                'width' => '100%',
                'min-height' => '70vh'
            ]
        ]);

        return $tag;
    }
}
