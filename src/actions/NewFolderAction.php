<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;


class NewFolderAction extends Action
{
    use ModuleAwareTrait;

    public function run()
    {

        $trans= Yii::$app->db->beginTransaction();

        FileTypeIconsAsset::register($this->controller->view);
        $uuid = Yii::$app->request->get('uuid', false);
        $parent = Directory::find()->uuid($uuid)->one();

        if (empty($parent))
            throw new NotFoundHttpException('Page not found');

        if(!AclHelper::canWrite($parent))
            throw new ForbiddenHttpException(Yii::t('filescatalog','You can not create items in this folder'));

        try {


            $model = new Directory();

            if ($model->load(Yii::$app->request->post()) && $model->appendTo($parent)->save()) {
                $acl = new AccessControl();
                $acl->inode_id = $model->id;
                $acl->user_id = Yii::$app->user->id;
                $acl->role= AccessControl::DUMMY_ROLE;
                $acl->crud_mask = AccessControl::ACTION_WRITE | AccessControl::ACTION_READ | AccessControl::ACTION_DELETE;
                $acl->save();

                $trans->commit();
                return $this->controller->redirect(['index', 'uuid' => $model->uuid]);
            }
        } catch (\Throwable $e) {
            $trans->rollBack();
        }

        return $this->controller->render('new-folder', [
            'model' => $model,
            'parent' => $parent
        ]);

    }
}
