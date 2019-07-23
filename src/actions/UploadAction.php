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
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\base\UserException;
use yii\web\Response;
use yii\web\UploadedFile;

class UploadAction extends Action
{
    /**
     * @var DefaultController
     */
    public $controller;

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

        $targetUuid = Yii::$app->request->post('target');

        if (empty($targetUuid))
            throw new UserException(Yii::t('xenon', 'Target not defined'));

        $targetNode = $this->controller->findModel($targetUuid, File::class);

        if ($targetNode->type == InodeTypes::TYPE_FILE) {
            $realParent = $targetNode->getParent()->one();

            if (empty($realParent))
                throw new InvalidArgumentException(Yii::t('xenon', 'Unable to get parent'));

            $model->setAsVersion($targetNode->uuid);
            $model->appendTo($realParent)->save();

        } else {
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
