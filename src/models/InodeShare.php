<?php


namespace eseperio\filescatalog\models;

use eseperio\filescatalog\models\base\InodeShare as BaseInodeShare;
use yii\web\ServerErrorHttpException;

/**
 * This model represents an inode shared between two users
 * It contains additional behaviors to attach read permissions to children directories.
 * Warning: if you use base/InodeShare, file permissions will not be added either removed.
 */
class InodeShare extends BaseInodeShare
{

    /**
     * @return array|int
     */
    public function transactions()
    {
        return self::OP_ALL;
    }

    /**
     * @param $insert
     * @param $changedAttributes
     * @return void
     */
    public function afterSave($insert, $changedAttributes)
    {
        AccessControl::grantAccessToUsers($this->inode, $this->user_id, AccessControl::ACTION_READ);
        $permModel = AccessControl::find()->where([
            'user_id' => $this->user_id,
            'role' => AccessControl::DUMMY_ROLE,
            'inode_id' => $this->inode_id
        ])->one();
        if (empty($permModel)) {
            throw new ServerErrorHttpException('Permission model was not found');
        }
        $permModel->copyPermissionToDescendants();

        parent::afterSave($insert, $changedAttributes);
    }
}
