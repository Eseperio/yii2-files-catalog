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
use yii\base\InvalidArgumentException;
use yii\db\Connection;
use yii\web\ServerErrorHttpException;

class InheritAcl extends Action
{
    use ModuleAwareTrait;

    public function run()
    {
        $permModel = Yii::createObject(InodePermissionsForm::class);
        $permModel->scenario = AccessControl::SCENARIO_DELETE;
        $permModel->setAttributes(Yii::$app->request->post(), false);
        if ($permModel->validate()) {
            try {
                $inode = Inode::find()->where(['id' => $permModel->inode_id])->one();
                $realModel = AccessControl::find()->where([
                    'user_id' => $permModel->user_id,
                    'role' => $permModel->role,
                    'inode_id' => $permModel->inode_id
                ])->one();

                if (empty($inode) || empty($realModel))
                    throw new InvalidArgumentException('Inode not found');

                $children = $inode->getDescendantsIds(null, true);

                $data = [];
                $delPk = ['OR'];
                foreach ($children as $child) {
                    $delPk[] = [
                        'user_id' => $permModel->user_id,
                        'role' => $permModel->role,
                        'inode_id' => $child
                    ];
                    $data[] = [
                        $permModel->user_id,
                        $permModel->role,
                        $child,
                        $realModel->crud_mask
                    ];
                }

                AccessControl::deleteAll($delPk);

                /** @var Connection $db */
                $db = Yii::$app->get($this->module->db);
                $db->createCommand()->batchInsert($this->module->inodeAccessControlTableName, [
                    'user_id',
                    'role',
                    'inode_id',
                    'crud_mask'
                ], $data)->execute();

            } catch (\Throwable $e) {
                throw $e;
            }
        } else {
            throw new ServerErrorHttpException(Yii::t('filescatalog', 'An error ocurred deleting this item'));
        }

        $inode = Inode::findOne($permModel->inode_id);

        return $this->controller->redirect(['properties', 'uuid' => $inode->uuid]);
    }
}
