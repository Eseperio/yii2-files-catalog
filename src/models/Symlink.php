<?php
/**
 * Copyright (c) 2019. Grupo Smart (Spain)
 *
 * This software is protected under Spanish law. Any distribution of this software
 * will be prosecuted.
 *
 * Developed by Waizabú <code@waizabu.com>
 * Updated by: erosdelalamo on 18/7/2019
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use yii\base\Exception;

class Symlink extends Inode
{
    use ModuleAwareTrait;

    public function getInodeType()
    {
        return InodeTypes::TYPE_SYMLINK;
    }


    /**
     * Responsible of deleting the inode on the filesystem
     * @return mixed
     */
    function deleteInode()
    {
        return true;
    }

    /**
     * Responsible of saving inode to the real filesystem
     * @param $path
     * @return mixed
     */
    function saveInode($path)
    {
        //Symlinks has no representation in filesystem
        return true;
    }
}
