<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use Ramsey\Uuid\Uuid;

/**
 * Class Inode
 * @package eseperio\filescatalog\models
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $extension
 * @property string $mime
 * @property int $type
 * @property int $parent_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 */
abstract class Inode extends \eseperio\filescatalog\models\base\Inode
{
    public function beforeSave($insert)
    {
        $this->type = $this->getInodeType();

        return parent::beforeSave($insert);
    }

    /**
     * @return int The type of inode
     */
    abstract function getInodeType();
}
