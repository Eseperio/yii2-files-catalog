<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;

/**
 * Class Directory
 * @package eseperio\filescatalog\models
 */
class Directory extends Inode
{
    use ModuleAwareTrait;

    /**
     * @return InodeQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        $inodeQuery = parent::find();
        $inodeQuery->onlyDirs();

        return $inodeQuery;
    }

    /**
     * @return int
     */
    public function getInodeType()
    {
        return InodeTypes::TYPE_DIR;
    }

    /**
     * Responsible of deleting the inode on the filesystem
     * @return mixed
     */
    function deleteInode()
    {
        $this->module
            ->getStorageComponent()
            ->deleteDir($this->getInodeRealPath());
    }

    public function afterSave($insert, $changedAttributes)
    {
//        We dont need to save directorys into filesystem
//        try {
//            $filesystem = $this->module
//                ->getStorageComponent();
//            $inodeRealPath = $this->getInodeRealPath();
//
//            if (!$filesystem->has($inodeRealPath) && !$filesystem->createDir($inodeRealPath)) {
//                $this->addError('name', Yii::t('filescatalog', 'Unable to create folder'));
//                $this->delete();
//
//                return false;
//            }
//        } catch (\Throwable $e) {
//
//        }


        return parent::afterSave($insert, $changedAttributes);
    }
}
