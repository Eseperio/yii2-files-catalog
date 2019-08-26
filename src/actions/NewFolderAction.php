<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
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


        try {
            if (empty($parent))
                throw new NotFoundHttpException('Page not found');

            $model = new Directory();

            if ($model->load(Yii::$app->request->post()) && $model->appendTo($parent)->save()) {
                $acl = new AccessControl();
                $acl->inode_id = $model->id;
                $acl->user_id = Yii::$app->user->id;
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
