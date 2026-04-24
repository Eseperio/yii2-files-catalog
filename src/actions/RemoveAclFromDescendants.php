<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodePermissionsForm;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\ServerErrorHttpException;

/**
 * Removes a specific ACL grant (matching user_id + role + crud_mask) from all
 * descendants of the given inode, leaving grants with a different crud_mask untouched.
 *
 * @property \eseperio\filescatalog\controllers\DefaultController $controller
 */
class RemoveAclFromDescendants extends Action
{
    use ModuleAwareTrait;

    /**
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\web\ServerErrorHttpException
     */
    public function run()
    {
        $permModel = Yii::createObject(InodePermissionsForm::class);
        $permModel->scenario = AccessControl::SCENARIO_DELETE;
        $permModel->setAttributes(Yii::$app->request->post(), false);
        if ($permModel->validate()) {
            // Call controller findModel, which performs access control to inode
            $this->controller->findModel($permModel->inode_id);

            $realModel = AccessControl::find()->where([
                'user_id' => $permModel->user_id,
                'role' => $permModel->role,
                'inode_id' => $permModel->inode_id,
                'crud_mask' => $permModel->crud_mask
            ])->one();

            if (!empty($realModel)) {
                $realModel->removeExactPermissionFromDescendants();
            }
        } else {
            throw new ServerErrorHttpException(Yii::t('filescatalog', 'An error ocurred deleting this item'));
        }

        $inode = Inode::findOne($permModel->inode_id);

        return $this->controller->redirect(['properties', 'uuid' => $inode->uuid]);
    }
}
