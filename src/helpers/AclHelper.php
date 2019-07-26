<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\helpers;


use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;

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
     * @param $inode
     * @param $permission
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    private static function can($inode, $permission)
    {
        $module = self::getModule();

        if ($module->enableACL && !$module->isAdmin()) {
            $user = Yii::$app->get($module->user);
            $userId = $module->getUserId();
            $grantAccess = false;
            foreach ($inode->accessControlList as $acl) {

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
                        $grantAccess = $acl->user_id == $userId;
                        break;
                    default:
                        $grantAccess = $user->can($acl->role);
                        break;
                }

                if ($grantAccess)
                    break;
            }
            if (!$grantAccess) {
                throw new $module->aclException;
            }
        }

        return true;


    }

    /**
     * @param $inode
     * @return bool
     */
    public static function canCreate($inode)
    {
        return self::can($inode, AccessControl::ACTION_CREATE);
    }

    /**
     * @param $inode
     * @return bool
     */
    public static function canUpdate($inode)
    {
        return self::can($inode, AccessControl::ACTION_UPDATE);
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
