<?php

use eseperio\filescatalog\models\AccessControl;
use yii\db\Migration;
use yii\db\Query;

/**
 * Stores share expiration on inherited ACL rows so expired directory shares
 * stop authorizing descendant queries as well as direct model checks.
 */
class m261014_120000_add_acl_expiration extends Migration
{
    public function safeUp()
    {
        $permissionTable = '{{%fcatalog_inodes_perm}}';
        $inodeTable = '{{%fcatalog_inodes}}';
        $shareTable = '{{%fcatalog_shares}}';

        $this->addColumn($permissionTable, 'expires_at', $this->integer()->null());

        $shares = (new Query())
            ->select(['inode_id', 'user_id', 'expires_at'])
            ->from($shareTable)
            ->where(['not', ['expires_at' => null]])
            ->all($this->db);

        foreach ($shares as $share) {
            $frontier = [(int)$share['inode_id']];
            $visited = [];
            while ($frontier) {
                $frontier = array_values(array_diff(array_map('intval', $frontier), $visited));
                if (!$frontier) {
                    break;
                }
                $visited = array_merge($visited, $frontier);
                $next = [];
                foreach (array_chunk($frontier, 500) as $batch) {
                    $this->update($permissionTable, ['expires_at' => (int)$share['expires_at']], [
                        'inode_id' => $batch,
                        'user_id' => (int)$share['user_id'],
                        'role' => AccessControl::DUMMY_ROLE,
                    ]);
                    $next = array_merge($next, (new Query())
                        ->select('id')
                        ->from($inodeTable)
                        ->where(['parent_id' => $batch])
                        ->column($this->db));
                }
                $frontier = $next;
            }
        }
    }

    public function safeDown()
    {
        $this->dropColumn('{{%fcatalog_inodes_perm}}', 'expires_at');
    }
}
