<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\InodePermissionsForm;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use eseperio\filescatalog\widgets\IconDisplay;
use Yii;
use yii\base\Action;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;

class PropertiesAction extends Action
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

        $permModel = new InodePermissionsForm();
        $permModel->inode_id = $model->id;

        if ($permModel->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ActiveForm::validate($permModel);
            }

            if ($permModel->save()) {
                $permModel = new InodePermissionsForm();
                $permModel->inode_id = $model->id;
                $model->refresh();
            }
        }


        return $this->controller->render('properties', [
            'model' => $model,
            'accessControlFormModel' => $permModel,
            'attributes' => $this->getAttributes($model),
        ]);

    }

    /**
     * @param $inode
     * @return array with the attributes to be displayed on detailview
     */
    public function getAttributes($inode)
    {
        switch ($inode->type) {
            case InodeTypes::TYPE_FILE:
            case InodeTypes::TYPE_VERSION:
                return $this->getFileAttributes($inode);
                break;
            default:
                return $this->getCommonAttributes($inode);
                break;
        }
    }

    /**
     * @param $inode
     * @return array
     */
    private function getFileAttributes($inode): array
    {
        return [
            'created_at:datetime',
            'author_name',
            [
                'attribute' => 'extension',
                'format' => 'raw',
                'visible' => $inode->type === InodeTypes::TYPE_FILE,
                'value' => function ($model) {
                    $html = IconDisplay::widget([
                        'model' => $model,
                        'iconSize' => IconDisplay::SIZE_MD
                    ]);

                    if ($model->type === InodeTypes::TYPE_FILE) {
                        $html .= " *." . Html::encode($model->extension);
                    }

                    return $html;
                }
            ],
            [
                'attribute' => 'filesize',
                'format' => [
                    'shortSize',
                    'decimals' => 0

                ]
            ],
            [
                'attribute' => 'md5hash',
                'visible' => Yii::$app->getModule('filex')->checkFilesIntegrity
            ],
            'mime',
            'uuid',
        ];
    }

    /**
     * @param $inode
     * @return array
     */
    private function getCommonAttributes($inode): array
    {
        return [
            'created_at:datetime',
            'author_name',
            'uuid',
        ];
    }
}
