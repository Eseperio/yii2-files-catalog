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
            $aclStatus = false;
            foreach ($inode->accessControlList as $acl) {
                if ($aclStatus)
                    continue;

                if ((($acl->role !== AccessControl::DUMMY_ROLE && $user->can($acl->role))
                        || $acl->user_id == $userId)
                    && (($acl->crud_mask & $permission) === $permission)) {
                    $aclStatus = true;
                }
            }
            if (!$aclStatus) {
                throw new $module->aclException;
            }
        }

        return true;


    }
}
