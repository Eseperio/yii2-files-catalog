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

    const TYPE_USER = 1;
    const TYPE_ROLE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return static::getModule()->inodeAccessControlTableName;
    }

    public static function grantAccessToUsers($file, $users, $mask)
    {

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

        if(is_null($mask))
            $mask= $filesCatalogModule->mask;
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
                if (is_object($file) && is_subclass_of($file, Inode::class)) {
                    $id = $file->id;
                } else {
                    $id = $file;
                }
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
}
