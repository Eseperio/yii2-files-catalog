<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\dictionaries\InodeTypes;
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
        /* @var $model File */
        $model->file = UploadedFile::getInstance($model, 'file');
        if ($model->validate(['file'])) {
            $response = [
                'name' => $model->file->name,
            ];
        }

        /* @todo: Check ACL */

        $targetNode = File::find()->uuid(Yii::$app->request->post('target'))->one();
        if ($targetNode->type == InodeTypes::TYPE_FILE) {
            $realParent = $targetNode->getParent()->one();
            $model->setAsVersion($targetNode->uuid);
            $model->appendTo($realParent)->save();

        } else {
            $model->parent_id = $targetNode->id;
            $model->appendTo($targetNode)->save();

        }

        if ($model->hasErrors())
            $response['errors'] = $model->errors;


        return [
            'files' => [
                $response,
            ],
        ];
    }
}
