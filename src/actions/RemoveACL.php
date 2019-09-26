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
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class RemoveACL extends Action
{
    public function run()
    {
        $permModel = Yii::createObject(InodePermissionsForm::class);
        $permModel->scenario = AccessControl::SCENARIO_DELETE;
        $permModel->setAttributes(Yii::$app->request->post(), false);
        if ($permModel->validate()) {
            try {
                $realModel = AccessControl::find()->where([
                    'user_id' => $permModel->user_id,
                    'role' => $permModel->role,
                    'inode_id' => $permModel->inode_id
                ])->one();
                if (!empty($realModel))
                    $realModel->delete();
            } catch (\Throwable $e) {
                throw $e;
            }
        } else {
            throw new ServerErrorHttpException(Yii::t('filescatalog', 'An error ocurred deleting this item'));
        }


        return $this->controller->goBack();
    }
}
