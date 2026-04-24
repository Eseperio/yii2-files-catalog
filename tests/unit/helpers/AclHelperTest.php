<?php

namespace unit\helpers;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeShare;
use Ramsey\Uuid\Uuid;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;

class AclHelperTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();
        Yii::$app->getModule('filex')->enableACL = true;
        Yii::$app->getModule('filex')->administratorPermissionName = 'adminPermission';
    }

    public function testCanReadHonorsExpiredAndActiveShares(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_C);

        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_C, AccessControl::ACTION_READ);
        $this->tester->haveInDatabase('fcatalog_shares', [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_C,
            'expires_at' => time() - 3600,
        ]);

        $inode = Inode::findOne($file->id);
        $this->assertFalse(AclHelper::canRead($inode));

        Yii::$app->db->createCommand()->delete('fcatalog_shares', [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_C,
        ])->execute();
        Yii::$app->db->createCommand()->insert('fcatalog_shares', [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_C,
            'expires_at' => time() + 3600,
        ])->execute();

        $this->assertTrue(AclHelper::canRead($inode));
    }

    public function testCanShareUsesWriteMaskAlias(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_C);

        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_C, AccessControl::ACTION_WRITE);

        $inode = Inode::findOne($file->id);
        $this->assertTrue(AclHelper::canShare($inode));
    }

    public function testCanDeleteFromLoggedInRoleGrant(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_C);

        AccessControl::grantAccessToRoles($file->id, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_DELETE);

        $inode = Inode::findOne($file->id);
        $this->assertTrue(AclHelper::canDelete($inode));
    }
}
