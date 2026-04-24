<?php

namespace unit\models;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use Ramsey\Uuid\Uuid;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;

class AccessControlTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();
    }

    public function testSetCrudBuildsBitMask(): void
    {
        $model = new AccessControl();
        $model->setCrud([
            AccessControl::ACTION_READ,
            AccessControl::ACTION_WRITE,
            AccessControl::ACTION_DELETE,
        ]);

        $this->assertSame(
            AccessControl::ACTION_READ | AccessControl::ACTION_WRITE | AccessControl::ACTION_DELETE,
            $model->crud_mask
        );
    }

    public function testGetCrudReturnsMaskFlagsForStoredRecord(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $file = $this->tester->grabFixture('inodes', 'file');

        $this->tester->haveInDatabase(AccessControl::tableName(), [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_A,
            'role' => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);

        $record = AccessControl::findOne([
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_A,
            'role' => AccessControl::DUMMY_ROLE,
        ]);

        $this->assertSame([
            AccessControl::ACTION_READ,
            AccessControl::ACTION_WRITE,
        ], $record->getCrud());
    }

    public function testGrantAndRemoveAccessToUserAndRole(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $file = $this->tester->grabFixture('inodes', 'file');

        $this->assertNotFalse(
            AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_READ)
        );
        $this->assertNotFalse(
            AccessControl::grantAccessToRoles($file->id, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_WRITE)
        );

        $this->tester->seeRecord(AccessControl::class, [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_A,
            'role' => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $this->tester->seeRecord(AccessControl::class, [
            'inode_id' => $file->id,
            'user_id' => AccessControl::DUMMY_USER,
            'role' => AccessControl::LOGGED_IN_USERS,
            'crud_mask' => AccessControl::ACTION_WRITE,
        ]);

        $this->assertTrue(AccessControl::removeAccessToUser($file->id, UserIdentity::USER_A));
        $this->tester->dontSeeRecord(AccessControl::class, [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_A,
            'role' => AccessControl::DUMMY_ROLE,
        ]);
        $this->tester->seeRecord(AccessControl::class, [
            'inode_id' => $file->id,
            'user_id' => AccessControl::DUMMY_USER,
            'role' => AccessControl::LOGGED_IN_USERS,
            'crud_mask' => AccessControl::ACTION_WRITE,
        ]);

        $this->assertTrue(AccessControl::removeAccessToRole($file->id, AccessControl::LOGGED_IN_USERS));
        $this->tester->dontSeeRecord(AccessControl::class, [
            'inode_id' => $file->id,
            'role' => AccessControl::LOGGED_IN_USERS,
        ]);
    }
}
