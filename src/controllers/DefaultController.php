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

namespace eseperio\filescatalog\controllers;


use eseperio\filescatalog\actions\FakeAction;
use eseperio\filescatalog\actions\IndexAction;
use eseperio\filescatalog\actions\NewFolderAction;
use eseperio\filescatalog\actions\PropertiesAction;
use eseperio\filescatalog\actions\UploadAction;
use eseperio\filescatalog\actions\ViewAction;
use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use yii\web\NotFoundHttpException;

class DefaultController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => ['class' => IndexAction::class],
            'upload' => ['class' => UploadAction::class],
            'new-folder' => ['class' => NewFolderAction::class],
            'properties' => ['class' => PropertiesAction::class],
            'view' => ['class' => ViewAction::class],
            'fake' => ['class' => FakeAction::class]
        ];
    }

    /**
     * @param $id
     * @param string $entity
     * @return Inode|File|Directory
     * @throws NotFoundHttpException
     */
    public function findModel($id, $entity = Inode::class)
    {
        $query = call_user_func([$entity, 'find']);
        if (strlen($id) === 36) {
            $query->uuid($id)->one();
        } else {
            $query->where(['id' => $id]);
        }
        if (($model = $query->one()) == null)
            throw new NotFoundHttpException();

        return $model;
    }
}
