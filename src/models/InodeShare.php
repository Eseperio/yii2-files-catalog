<?php


namespace eseperio\filescatalog\models;

use eseperio\filescatalog\models\base\InodeShare as BaseInodeShare;
use yii\web\ServerErrorHttpException;

/**
 * This model represents an inode shared between two users
 * It contains additional behaviors to attach read permissions to children directories.
 * Warning: if you use base/InodeShare, file permissions will not be added either removed.
 * @property \eseperio\filescatalog\models\AccessControl $accessControl
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
     * Return the user related
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->module->identityClass, [$this->module->userIdAttribute => 'user_id']);
    }

    /**
     * The access control entity associated to this share
     * @return \yii\db\ActiveQuery
     */
    public function getAccessControl()
    {
        return $this->hasOne(AccessControl::class, ['inode_id' => 'inode_id', 'user_id' => 'user_id'])->where([
            'role' => AccessControl::DUMMY_ROLE,
        ]);
    }

    /**
     * @param $insert
     * @param $changedAttributes
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function afterSave($insert, $changedAttributes)
    {
        self::deleteAll([
            'AND',
            ['user_id' => $this->user_id],
            ['inode_id' => $this->inode_id],
            ['<', 'expires_at', time()],
        ]);
        AccessControl::grantAccessToUsers($this->inode, $this->user_id, AccessControl::ACTION_READ);
        $this->refresh();
        $permModel = $this->accessControl;
        if (empty($permModel)) {
            throw new ServerErrorHttpException('Permission model was not found');
        }

        $permModel->copyPermissionToDescendants();

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Delete granted permissions to children
     * @return void
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function afterDelete()
    {
        $permModel = $this->accessControl;
        if (!empty($permModel)) {
            $permModel->removeSiblingsRecursive();
            $permModel->delete();
        }

        parent::afterDelete();
    }
}
