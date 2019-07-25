<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\InodePermissionsForm;
use Yii;
use yii\base\Action;

class RemoveACL extends Action
{
    public function run()
    {
        $permModel = new InodePermissionsForm();
        $permModel->scenario = AccessControl::SCENARIO_DELETE;
        $permModel->load(Yii::$app->request->post(), '');
        if ($permModel->validate())
            try {
                $realModel = AccessControl::find()->where([
                    'user_id' => $permModel->user_id,
                    'role' => $permModel->role,
                    'inode_id' => $permModel->inode_id
                ])->one();
                $realModel->delete();
            } catch (\Throwable $e) {
                throw $e;
            }


        return $this->controller->goBack();
    }
}
