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
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\FileVersion;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ContainerStaticHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;

/**
 * Class AclHelper
 * @package eseperio\filescatalog\helpers
 */
class AclHelper extends Component
{

    use ModuleAwareTrait;
    use ContainerStaticHelper;

    /**
     * @var
     */
    private $inode;

    public function __construct($inode, array $config = [])
    {
        $this->inode = $inode;
        parent::__construct($config);
    }

    /**
     * @param $inode
     * @return bool
     */
    public static function canRead($inode)
    {
        return self::can($inode, AccessControl::ACTION_READ);
    }

    /**
     * @param $inode Inode|Directory
     * @param $permission
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public static function can($inode, $permission)
    {
        /** @var AclHelper $helper */
        $helper = Yii::createObject(__CLASS__, [$inode]);

        return $helper->checkAccess($permission);
    }

    /**
     * @param $permission
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function checkAccess($permission)
    {
        $inode = $this->inode;

        if (empty($inode))
            throw new InvalidArgumentException('Inode cannot be empty');

        $module = self::getModule();

        if (!self::getModule()->enableACL)
            return true;

        if ($inode['type'] === InodeTypes::TYPE_VERSION) {
            $version = FileVersion::findOne(['version_id' => $inode['id']]);
            $inode = $version->original;

        }

        if (!in_array($inode['type'], [InodeTypes::TYPE_DIR, InodeTypes::TYPE_FILE, InodeTypes::TYPE_SYMLINK]))
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
                    case AccessControl::INHERIT_FROM_PARENT:
                        $parent = $inode->getParent()->one();
                        if (!empty($parent))
                            $grantAccess = self::can($parent, $permission);
                        break;
                    case AccessControl::LOGGED_IN_USERS:
                        $grantAccess = !Yii::$app->get($module->user)->getIsGuest();
                        break;
                    case AccessControl::DUMMY_ROLE:
                        $grantAccess = $acl['user_id'] == $userId;
                        break;
                    default:
                        if (!empty(trim($acl['role'])))
                            $grantAccess = $user->can($acl['role']);
                        break;
                }


                if ($grantAccess)
                    break;
            }

            return $grantAccess;

        }

        return true;

    }

    /**
     * @param $inode
     * @return bool
     */
    public static function canWrite($inode)
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
