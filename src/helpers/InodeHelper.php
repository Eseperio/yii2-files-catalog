<?php

namespace eseperio\filescatalog\helpers;

use eseperio\filescatalog\models\Directory;
use yii\helpers\FileHelper;

/**
 * Implements useful methods for working with files and directories.
 * All methods are static and are shorthands to available utilities in filescatalog library
 * @todo: WIP
 */
class InodeHelper
{
    public static function addDirectory($to, $relPath)
    {
        $relPath = FileHelper::normalizePath($relPath);
        $relPath = trim($relPath, '/');
        $relPath = explode('/', $relPath);
        $parent = $to;
        if (!is_a($to, Directory::class)) {
            throw new \InvalidArgumentException("First argument must be a Directory instance");
        }
        foreach ($relPath as $dirName) {
            $dir = new Directory([
                'parent_id' => $parent->id,
                'name' => $dirName
            ]);
            $dir->save();
            $parent = $dir;
        }


        return $parent;
    }
}
