<?php

namespace unit\models;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeShare;
use Ramsey\Uuid\Uuid;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;

class InodeShareTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();
    }

    public function testSaveCreatesAclAndPropagatesToDescendants(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $dir = $this->tester->grabFixture('inodes', 'dir');
        $childId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'shared_child',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $dir->id,
            'uuid' => Uuid::uuid4()->toString(),
            'depth' => 2,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        $share = new InodeShare();
        $share->inode_id = $dir->id;
        $share->user_id = UserIdentity::USER_C;
        $share->expires_at = time() + 3600;

        $this->assertTrue($share->save(false));

        $this->tester->seeRecord(AccessControl::class, [
            'inode_id' => $dir->id,
            'user_id' => UserIdentity::USER_C,
            'role' => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $this->tester->seeRecord(AccessControl::class, [
            'inode_id' => $childId,
            'user_id' => UserIdentity::USER_C,
            'role' => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
    }

    public function testDeleteRemovesAclAndDescendantPermissions(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $dir = $this->tester->grabFixture('inodes', 'dir');
        $childId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'shared_child_to_remove',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $dir->id,
            'uuid' => Uuid::uuid4()->toString(),
            'depth' => 2,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        $share = new InodeShare();
        $share->inode_id = $dir->id;
        $share->user_id = UserIdentity::USER_C;
        $share->expires_at = time() + 3600;
        $share->save(false);

        $this->assertTrue($share->delete() !== false);

        $this->tester->dontSeeRecord(AccessControl::class, [
            'inode_id' => $dir->id,
            'user_id' => UserIdentity::USER_C,
            'role' => AccessControl::DUMMY_ROLE,
        ]);
        $this->tester->dontSeeRecord(AccessControl::class, [
            'inode_id' => $childId,
            'user_id' => UserIdentity::USER_C,
            'role' => AccessControl::DUMMY_ROLE,
        ]);
    }
}
