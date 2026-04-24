<?php

namespace unit\models;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeQuery;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;
use yii\base\InvalidArgumentException;

class InodeQueryTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();

        $module = Yii::$app->getModule('filex');
        $module->enableACL = true;
        $module->enableUserSharing = true;
    }

    public function testPrefixUsesInodeTableNameByDefault(): void
    {
        $this->assertSame('fcatalog_inodes.uuid', InodeQuery::prefix('uuid'));
    }

    public function testBasicQueryBuildersAddExpectedSqlClauses(): void
    {
        $sql = Inode::find()
            ->uuid('abc-123')
            ->byName('sample', true)
            ->byType([InodeTypes::TYPE_FILE])
            ->excludeVersions()
            ->orderByExtension()
            ->orderByType()
            ->orderAZ()
            ->createCommand()
            ->getRawSql();

        $this->assertStringContainsString('`fcatalog_inodes`.`uuid`', $sql);
        $this->assertStringContainsString('`fcatalog_inodes`.`name`', $sql);
        $this->assertStringContainsString('`fcatalog_inodes`.`type`', $sql);
        $this->assertStringContainsString('`fcatalog_inodes`.`extension`', $sql);
        $this->assertStringContainsString('LIKE', $sql);
    }

    public function testFormatFiltersAddTheExpectedTypeConstraints(): void
    {
        $filesSql = Inode::find()->onlyFiles()->createCommand()->getRawSql();
        $dirsSql = Inode::find()->onlyDirs()->createCommand()->getRawSql();
        $symlinkSql = Inode::find()->onlySymlinks()->createCommand()->getRawSql();

        $this->assertStringContainsString((string) InodeTypes::TYPE_FILE, $filesSql);
        $this->assertStringContainsString((string) InodeTypes::TYPE_DIR, $dirsSql);
        $this->assertStringContainsString((string) InodeTypes::TYPE_SYMLINK, $symlinkSql);
    }

    public function testWithSymlinksReferencesSelectsSymlinkColumns(): void
    {
        $sql = Inode::find()->withSymlinksReferences()->createCommand()->getRawSql();

        $this->assertStringContainsString('LEFT OUTER JOIN', $sql);
        $this->assertStringContainsString('symlink_name', $sql);
        $this->assertStringContainsString('symlink_type', $sql);
        $this->assertStringContainsString('symlink_extension', $sql);
    }

    public function testOnlyReadableWriteableAndDeletableFilterByCrudMask(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $root = $this->tester->grabFixture('inodes', 'root');
        $readableId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'readable',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);
        $writeableId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'writeable',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);
        $deletableId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'deletable',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        AccessControl::grantAccessToRoles($readableId, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_READ);
        AccessControl::grantAccessToRoles($writeableId, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_WRITE);
        AccessControl::grantAccessToRoles($deletableId, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_DELETE);

        $this->tester->amLoggedInAs(UserIdentity::USER_A);

        $readableIds = Inode::find()->onlyReadable()->select('id')->column();
        $writeableIds = Inode::find()->onlyWriteable()->select('id')->column();
        $deletableIds = Inode::find()->onlyDeletable()->select('id')->column();

        $this->assertContains($readableId, $readableIds);
        $this->assertNotContains($writeableId, $readableIds);
        $this->assertNotContains($deletableId, $readableIds);

        $this->assertContains($writeableId, $writeableIds);
        $this->assertNotContains($readableId, $writeableIds);
        $this->assertNotContains($deletableId, $writeableIds);

        $this->assertContains($deletableId, $deletableIds);
        $this->assertNotContains($readableId, $deletableIds);
        $this->assertNotContains($writeableId, $deletableIds);
    }

    public function testWithSharesCountsAllAndOnlyActiveShares(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->haveInDatabase('fcatalog_shares', [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_A,
            'expires_at' => time() + 3600,
        ]);
        $this->tester->haveInDatabase('fcatalog_shares', [
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_C,
            'expires_at' => time() - 3600,
        ]);

        $module = Yii::$app->getModule('filex');
        $module->enableUserSharing = true;

        $allShares = Inode::find()->where(['id' => $file->id])->withShares()->one();
        $activeShares = Inode::find()->where(['id' => $file->id])->withSharesActive()->one();

        $this->assertSame(2, (int) $allShares->shared);
        $this->assertSame(1, (int) $activeShares->shared);
    }

    public function testSharedWithMeOnlyReturnsItemsWithActiveShares(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $root = $this->tester->grabFixture('inodes', 'root');
        $activeId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'active share',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);
        $expiredId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'expired share',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        AccessControl::grantAccessToRoles($activeId, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_READ);
        AccessControl::grantAccessToRoles($expiredId, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_READ);
        $this->tester->haveInDatabase('fcatalog_shares', [
            'inode_id' => $activeId,
            'user_id' => UserIdentity::USER_A,
            'expires_at' => time() + 3600,
        ]);
        $this->tester->haveInDatabase('fcatalog_shares', [
            'inode_id' => $expiredId,
            'user_id' => UserIdentity::USER_A,
            'expires_at' => time() - 3600,
        ]);

        $this->tester->amLoggedInAs(UserIdentity::USER_A);

        $ids = Inode::find()->sharedWithMe()->select('id')->column();

        $this->assertContains($activeId, $ids);
        $this->assertNotContains($expiredId, $ids);
    }

    public function testConflictingTypeFiltersThrowAnException(): void
    {
        $query = Inode::find();
        $query->onlyFiles();

        $this->expectException(InvalidArgumentException::class);
        $query->onlyDirs();
    }
}
