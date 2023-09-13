<?php

use yii\db\Migration;

/**
 * Class m220426_093025_add_shares_table
 */
class m220426_093025_add_shares_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tablename = '{{%fcatalog_shares}}';
        $this->createTable($tablename, [
            'inode_id' => $this->integer()->comment('Inode id'),
            'user_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'author_name' => $this->string(128),
            'editor_name' => $this->string(128),
            'expires_at' => $this->integer()->comment('Since which date the shared item will stop working')
        ]);
        $this->addPrimaryKey('fcatalog_shares_pk', $tablename, [
            'inode_id',
            'user_id'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%fcatalog_shares}}');
    }

}
