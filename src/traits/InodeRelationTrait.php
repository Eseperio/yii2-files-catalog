<?php

namespace eseperio\filescatalog\traits;

use eseperio\filescatalog\models\Inode;

/**
 * @property Inode|null $inode
 */
trait InodeRelationTrait
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInode()
    {
        return $this->hasOne(\eseperio\filescatalog\models\Inode::class, ['id' => 'inode_id']);
    }
}
