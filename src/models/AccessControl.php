<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\db\ActiveRecord;
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
    /**
     * This role is used to keep primary index working
     */
    const DUMMY_ROLE = 'filexdmr';

    const DUMMY_USER = 0;
    const TYPE_USER = 1;
    const TYPE_ROLE = 2;

    const WILDCARD_ROLE = '*';
    const LOGGED_IN_USERS = '@';


    const ACTION_READ = 4;
    const ACTION_WRITE = 2;
    const ACTION_DELETE = 1;

    const SCENARIO_DELETE = 'delscen';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return static::getModule()->inodeAccessControlTableName;
    }

    /**
     * Allow one or many users to access a file.
     * @param $files
     * @param $users
     * @param null $mask
     * @return bool|int
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
                    $rows[] = [
                        $id,
                        $userId,
                        null,
                        $mask
                    ];
                } else {
                    $rows[] = [
                        $id,
                        null,
                        $userId,
                        $mask
                    ];
                }

            }
        }
        try {
            return $command = \Yii::$app->db->createCommand()
                ->batchInsert($filesCatalogModule->inodeAccessControlTableName, $columns, $rows)->execute();
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
        if (is_object($item) && is_subclass_of($item, Inode::class))
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
        return self::setInodesAccessRules($files, $roles, $mask);
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DELETE => ['inode_id', 'user_id', 'role', 'crud'],
            self::SCENARIO_DEFAULT => ['inode_id', 'user_id', 'role', 'crud'],
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                ['role', 'user_id'],
                'unique',
                'targetAttribute' =>
                    [
                        'inode_id',
                        'role',
                        'user_id'
                    ],
                'message' => Yii::t('filescatalog', 'This permission is already assigned'),
                'on' => self::SCENARIO_DEFAULT
            ]
        ];
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
}
