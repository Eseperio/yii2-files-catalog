<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\File;
use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Yii::createObject(File::class);
        $model->file = UploadedFile::getInstance($model, 'file');

        if ($model->validate(['file'])) {
            $response = [
                'name' => $model->file->name,
            ];
        }

        $root = Inode::find()->roots()->one();
        /* @todo: Check ACL */

        $targetNode = Inode::find()->uuid(Yii::$app->request->post('target'))->one();

        $model->parent_id = $targetNode->id;
        $model->appendTo($targetNode ?? $root);
        if ($model->hasErrors())
            $response['errors'] = $model->errors;

        return [
            'files' => [
                $response,
            ],
        ];
    }
}
