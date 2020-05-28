<?php
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
    private $inodeVersionsTableName = "fcatalog_inodes_version";

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->inodeTableName, [
            'id' => 'int(11) NOT NULL AUTO_INCREMENT = 10 PRIMARY KEY',
            'uuid' => $this->string(36),
            'name' => $this->string(255),
            'extension' => $this->string(16),
            'mime' => $this->string(128),
            'type' => $this->integer(1)->defaultValue(InodeTypes::TYPE_FILE)->notNull(),
            'parent_id' => $this->integer()->defaultValue(0),
            'md5hash' => $this->string(32),
            'depth' => $this->integer()->notNull(),
            'filesize' => $this->bigInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'author_name' => $this->string(128),
            'editor_name' => $this->string(128)
        ],'AUTO_INCREMENT = 10');
        $this->createIndex('idx_name_ext_inode', $this->inodeTableName, [
            'uuid',
            'type'
        ]);

        $this->createIndex('parent_id_index', $this->inodeTableName, [
            'parent_id'
        ]);

        $this->createTable($this->inodePermissionTableName, [
            'inode_id' => $this->integer()->comment('Inode id'),
            'user_id' => $this->integer(),
            'role' => $this->string(64),
            'crud_mask'=>$this->smallInteger()->comment('4 bit Binary mask for access permission.CRUD = C=>8 R=>4 U=>2 D=>1')->defaultValue(4)
        ]);
        $this->addPrimaryKey('inode_perms', $this->inodePermissionTableName, [
            'inode_id',
            'user_id',
            'role'
        ]);


        $this->createTable($this->inodeVersionsTableName, [
            'file_id' => $this->integer(),
            'version_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'author_name' => $this->string(128),
            'editor_name' => $this->string(128),
        ]);

        $this->addPrimaryKey('inode_versions_pk', $this->inodeVersionsTableName, [
            'file_id',
            'version_id'
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
