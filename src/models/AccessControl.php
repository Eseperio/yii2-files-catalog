<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\traits\InodeRelationTrait;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

/**
 * Class AccessControl
 * @package eseperio\filescatalog\models
 * @property $inode_id
 * @property $user_id
 * @property $role
 * @property $crud_mask
 */
class AccessControl extends ActiveRecord
{

    use ModuleAwareTrait;
    use InodeRelationTrait;

    /**
     * This role is used to keep primary index working
     */
    const DUMMY_ROLE = 'filexdmr';

    const DUMMY_USER = 0;
    const TYPE_USER = 1;
    const TYPE_ROLE = 2;

    const WILDCARD_ROLE = '*';
    const LOGGED_IN_USERS = '@';

    /**
     * Binary constants for CRUD_MASK
     */
    const ACTION_READ = 4;
    const ACTION_WRITE = 2;
    const ACTION_DELETE = 1;
    /**
     * Share action is defined in fourth bit, but not included in representation or
     * in queries filters. It is used by accessControl as an alias for ACTION_WRITE and
     * it helps to develop complex sharing rules in your own code.
     */
    const ACTION_SHARE = 8;


    const SCENARIO_DELETE = 'delscen';

    /**
     * @var array buffer for descendant ids
     */
    private $_descentantIds = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return static::getModule()->inodeAccessControlTableName;
    }

    /**
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function copyPermissionToDescendants()
    {
        if ($this->isNewRecord) {
            throw new InvalidConfigException('Permissions can only be copied from a stored record');
        }
        $inode = $this->inode;
        $inode->childrenJoinLevels = 5;
        $children = $this->getDescendantsIds($inode);

        $this->removeSiblingsRecursive();
        if (count($children) > 200) {
            Yii::$app->db->enableLogging = false;
            Yii::$app->db->enableProfiling = false;
            Yii::debug('Disabled db logging for bulk insert of inode descendants permissions due to large amount of records', 'filescatalog');
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $batchSize = 1000;
            $batchLoop = 0;// iterate children in batches
            while (!empty($children)) {
                $data = [];
                $batch = array_splice($children, 0, $batchSize);
                foreach ($batch as $child) {
                    $data[] = [
                        $this->user_id,
                        $this->role,
                        $child,
                        $this->crud_mask
                    ];
                }

                /** @var Connection $db */
                $db = Yii::$app->get($this->module->db);
                $db->createCommand()->batchInsert($this->module->inodeAccessControlTableName, [
                    'user_id',
                    'role',
                    'inode_id',
                    'crud_mask'
                ], $data)->execute();
                $batchLoop++;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        } finally {
            Yii::$app->db->enableLogging = true;
            Yii::$app->db->enableProfiling = true;
        }
    }

    /**
     * Removes all sibling permissions in all the inode descendants
     * @return void
     */
    public function removeSiblingsRecursive()
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $inode = $this->inode;
            $children = $this->getDescendantsIds($inode);// disable logging if children is greater than 200
            $totalRows = count($children);
            if ($totalRows > 200) {
                Yii::debug('Disabled db logging for bulk delete of inode descendants permissions', 'filescatalog');
                Yii::$app->db->enableLogging = false;
                Yii::$app->db->enableProfiling = false;
            }
            $batchSize = 1000;
            $batchLoop = 0;
            while (!empty($children)) {
                $batch = array_splice($children, 0, $batchSize);
                $delPk = [
                    'AND',
                    ['user_id' => $this->user_id],
                    ['role' => $this->role],
                    ['inode_id' => $batch]
                ];
                self::deleteAll($delPk);

                $batchLoop++;
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        } finally {
            Yii::$app->db->enableLogging = true;
            Yii::$app->db->enableProfiling = true;
        }


    }

    /**
     * Allow one or many users to access a file.
     * @param $files
     * @param $users
     * @param null $mask
     * @return bool|int
     * @throws \Exception
     */
    public static function grantAccessToUsers($files, $users, $mask = null)
    {
        return self::setInodesAccessRules($files, $users, $mask);
    }

    /**
     * @param $files
     * @param $usersOrRoles
     * @param $mask
     * @param int $type
     * @return bool|int the number of rows inserted
     * @throws \Exception
     */
    private static function setInodesAccessRules($files, $usersOrRoles, $mask = null, $type = self::TYPE_USER)
    {
        $filesCatalogModule = self::getModule();

        if (is_null($mask))
            $mask = $filesCatalogModule->defaultACLmask;


        $usersList = is_array($usersOrRoles) ? $usersOrRoles : [$usersOrRoles];
        $filesList = is_array($files) ? $files : [$files];

        $columns = [
            'inode_id',
            'user_id',
            'role',
            'crud_mask'
        ];
        $rows = [];
        foreach ($usersList as $user) {

            if (is_object($user)) {
                $userId = ArrayHelper::getValue($user, $filesCatalogModule->userIdAttribute);
            } else {
                $userId = $user;
            }
            foreach ($filesList as $file) {
                $id = self::getInodeRealId($file);
                if ($type === self::TYPE_USER) {
                    $rows[$userId . '-' . $id] = [
                        $id,
                        $userId,
                        AccessControl::DUMMY_ROLE,
                        $mask
                    ];
                } else {
                    $rows[$userId . '-' . $id] = [
                        $id,
                        AccessControl::DUMMY_USER,
                        $userId,
                        $mask
                    ];
                }

            }
        }
        try {

            $command = Yii::$app->db->createCommand()->batchInsert($filesCatalogModule->inodeAccessControlTableName, $columns, $rows);

            return $command->execute();
        } catch (\Throwable $e) {
            \Yii::debug($e->getMessage(), 'error');
        }

        return false;


    }

    /**
     * @param $item
     * @return int the id
     */
    private static function getInodeRealId($item)
    {
        if (is_object($item) && is_subclass_of($item, \eseperio\filescatalog\models\base\Inode::class))
            return $item->id;

        return $item;
    }

    /**
     * Removes access to one or many files for a user
     * @param $files
     * @param $user
     * @return bool
     */
    public static function removeAccessToUser($files, $user)
    {
        $ids = [];
        $filesCatalogModule = self::getModule();
        if (is_object($user)) {
            $userId = ArrayHelper::getValue($user, $filesCatalogModule->userIdAttribute);
        } else {
            $userId = $user;
        }
        if (is_array($files)) {
            foreach ($files as $file) {
                $ids[] = self::getInodeRealId($file);
            }
        } else {
            $ids = [self::getInodeRealId($files)];
        }

        $condition = [
            'inode_id' => $ids,
            'user_id' => $userId
        ];

        $rowsAffected = self::deleteAll($condition);

        return !$rowsAffected === false;
    }

    /**
     * Removes all role access to the file or files specified
     * Delete all returns number of rows affected, so it can be a false false reponse
     * @param $files
     * @param $role
     * @return bool
     */
    public static function removeAccessToRole($files, $role)
    {
        $ids = [];
        if (is_array($files)) {
            foreach ($files as $file) {
                $ids[] = self::getInodeRealId($file);
            }
        } else {
            $ids = [self::getInodeRealId($files)];
        }

        $condition = [
            'inode_id' => $ids,
            'role' => $role
        ];

        $rowsAffected = self::deleteAll($condition);

        return !$rowsAffected === false;
    }

    /**
     * @param $files
     * @param $roles
     * @param null $mask
     * @return bool|int
     */
    public static function grantAccessToRoles($files, $roles, $mask = null)
    {
        return self::setInodesAccessRules($files, $roles, $mask, self::TYPE_ROLE);
    }

    /**
     * @inheritDoc
     * @return array|int
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL
        ];
    }

    public function attributeLabels()
    {
        return [
            'crud_mask' => Yii::t('filescatalog', 'Permissions'),
            'role' => Yii::t('filescatalog', 'Role'),
            'user_id' => Yii::t('filescatalog', 'User id'),
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DELETE => ['inode_id', 'user_id', 'role', 'crud', 'crud_mask'],
            self::SCENARIO_DEFAULT => ['inode_id', 'user_id', 'role', 'crud', 'crud_mask'],
        ];
    }

    public function beforeSave($insert)
    {
        $oldPermission = self::find()->where([
            'user_id' => $this->user_id,
            'role' => $this->role,
            'inode_id' => $this->inode_id
        ])->all();
        $response = true;
        if (!empty($oldPermission)) {
            foreach ($oldPermission as $old) {
                $response &= (bool)$old->delete();
            }
        }

        if (!$response)
            return false;

        return parent::beforeSave($insert);
    }


    /**
     * @return array with each action and if it is enabled or not
     */
    public function getCrud()
    {
        $values = [
            self::ACTION_READ,
            self::ACTION_WRITE,
            self::ACTION_DELETE,
        ];

        if ($this->isNewRecord)
            return $this->module->defaultInodePermissions;

        $crud = [];
        foreach ($values as $value) {
            if (($this->crud_mask & $value) === $value)
                $crud[] = $value;
        }

        return $crud;
    }

    /**
     * @param mixed $crud
     */
    public function setCrud($crud): void
    {
        if (is_array($crud)) {
            $this->crud_mask = 0;
            foreach ($crud as $value) {
                $this->crud_mask |= (int)$value;
            }
        }
    }

    /**
     * @param \eseperio\filescatalog\models\Inode|null $inode
     * @return array
     */
    private function getDescendantsIds(?Inode $inode): array
    {
        if (empty($this->_descentantIds)) {
            $this->_descentantIds = $inode->getDescendantsIds(null, true);
        }
        return $this->_descentantIds;
    }
}
