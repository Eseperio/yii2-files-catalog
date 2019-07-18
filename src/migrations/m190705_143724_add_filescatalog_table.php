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

use eseperio\filescatalog\dictionaries\InodeTypes;
use yii\db\Migration;

/**
 * Class m190705_143724_add_filescatalog_table
 */
class m190705_143724_add_filescatalog_table extends Migration
{

    private $inodeTableName = "fcatalog_inodes";
    private $inodePermissionTableName = "fcatalog_inodes_perm";

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->inodeTableName, [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(36),
            'name' => $this->string(255),
            'extension' => $this->string(16),
            'mime' => $this->string(128),
            'type' => $this->integer(1)->defaultValue(InodeTypes::TYPE_FILE)->notNull(),
            'parent_id' => $this->integer()->defaultValue(0)->notNull(),
            'md5hash' => $this->string(32),
            //'tree' => $this->integer()->notNull(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
            'depth' => $this->integer()->notNull(),
            'filesize' => $this->bigInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
        ]);
        $this->createIndex('idx_name_ext_inode', $this->inodeTableName, [
            'uuid',
            'name',
            'type',
            'mime'
        ]);
        $this->createIndex('nests-idx', $this->inodeTableName, [
            'lft',
            'rgt'
        ]);

        $this->createTable($this->inodePermissionTableName, [
            'inode_id' => $this->integer()->comment('Inode id'),
            'user_id' => $this->integer(),
            'role' => $this->string(64)
        ]);
        $this->addPrimaryKey('inode_perms', $this->inodePermissionTableName, [
            'inode_id',
            'user_id',
            'role'
        ]);

    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->inodePermissionTableName);
        $this->dropTable($this->inodeTableName);
    }

}
