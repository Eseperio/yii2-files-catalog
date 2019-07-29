<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\helpers;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\InvalidArgumentException;

/**
 * Class AclHelper
 * @package eseperio\filescatalog\helpers
 */
class AclHelper
{

    use ModuleAwareTrait;

    /**
     * @param $inode
     * @return bool
     */
    public static function canRead($inode)
    {
        return self::can($inode, AccessControl::ACTION_READ);
    }

    /**
     * @param $inode Inode|File|Directory
     * @param $permission
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    private static function can($inode, $permission)
    {
        $module = self::getModule();
        if (!in_array($inode['type'], [InodeTypes::TYPE_DIR, InodeTypes::TYPE_FILE]))
            throw new InvalidArgumentException(__METHOD__ . " only accepts Files and directories");

        if ($module->enableACL && !$module->isAdmin()) {
            $user = Yii::$app->get($module->user);
            $userId = $module->getUserId();
            $grantAccess = false;
            foreach ($inode['accessControlList'] as $acl) {

                if (($acl->crud_mask & $permission) !== $permission)
                    continue;

                switch ($acl->role) {
                    case AccessControl::WILDCARD_ROLE:
                        $grantAccess = true;
                        break;
                    case AccessControl::LOGGED_IN_USERS:
                        $grantAccess = !Yii::$app->get($module->user)->getIsGuest();
                        break;
                    case AccessControl::DUMMY_ROLE:
                        $grantAccess = $acl['user_id'] == $userId;
                        break;
                    default:
                        $grantAccess = $user->can($acl['role']);
                        break;
                }

                if ($grantAccess)
                    break;
            }
            if (!$grantAccess) {
                return false;
            }
        }

        return true;

    }
    
    /**
     * @param $inode
     * @return bool
     */
    public static function cantWrite($inode)
    {
        return self::can($inode, AccessControl::ACTION_WRITE);
    }

    /**
     * @param $inode
     * @return bool
     */
    public static function canDelete($inode)
    {
        return self::can($inode, AccessControl::ACTION_DELETE);
    }
}
