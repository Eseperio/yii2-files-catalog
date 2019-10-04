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
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
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
     * @return array|Response
     * @throws \yii\base\InvalidConfigException
     */
    public function run()
    {

        Yii::$app->response->format = Response::FORMAT_JSON;
        $model = Yii::createObject(Inode::class);
        $model->type = InodeTypes::TYPE_FILE;

        /* @var $model Inode */
        $model->file = UploadedFile::getInstance($model, 'file');
        if ($model->validate(['file'])) {
            $response = [
                'name' => $model->file->name,
            ];
        }
        $targetUuid = Yii::$app->request->post('target');

        if (empty($targetUuid))
            throw new UserException(Yii::t('filescatalog', 'Target not defined'));

        $targetNode = $this->controller->findModel($targetUuid, Inode::class);

        AclHelper::canWrite($targetNode);

        if ($targetNode->type == InodeTypes::TYPE_FILE) {
            $realParent = $targetNode->getParent()->one();

            if (empty($realParent))
                throw new InvalidArgumentException(Yii::t('filescatalog', 'Unable to get parent'));

            $model->setAsVersion($targetNode->uuid);
            if ($model->appendTo($realParent)->save())
                return $this->controller->redirect(['view', 'uuid' => $model->uuid]);
        } else {
            $model->appendTo($targetNode)->save();

        }

        if ($model->hasErrors())
            $response['errors'] = $model->errors;
        elseif (Yii::$app->user->id) {
            $acl = Yii::createObject(AccessControl::class);
            $acl->inode_id = $this->id;
            $acl->user_id = Yii::$app->user->id;
            $acl->role = AccessControl::DUMMY_ROLE;
            $acl->crud_mask = AccessControl::ACTION_WRITE | AccessControl::ACTION_READ | AccessControl::ACTION_DELETE;
            $acl->save();
        }


        return [
            'files' => [
                $response,
            ],
        ];
    }


}
