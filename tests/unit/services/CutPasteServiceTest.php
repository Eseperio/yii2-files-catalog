<?php

namespace unit\services;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\services\CutPasteService;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;
use yii\web\ForbiddenHttpException;

class CutPasteServiceTest extends Unit
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
        $module->allowCutPaste = true;
    }

    public function testCutInodeStoresUuidAndReturnsTheCutItem(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_A);
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        $service = Yii::createObject(CutPasteService::class);

        $this->assertTrue($service->cutInode($file));
        $this->assertSame([$file->uuid], $service->getCutInodeUuids());
        $this->assertTrue($service->hasCutInodes());

        $cutInodes = $service->getCutInodes();
        $this->assertCount(1, $cutInodes);
        $this->assertSame($file->id, $cutInodes[0]->id);
    }

    public function testCutInodeRejectsWhenCutPasteIsDisabled(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_A);
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        Yii::$app->getModule('filex')->allowCutPaste = false;
        $service = Yii::createObject(CutPasteService::class);

        $this->expectException(ForbiddenHttpException::class);
        $service->cutInode($file);
    }

    public function testCutInodesIgnoresItemsWithoutWritePermission(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $root = $this->tester->grabFixture('inodes', 'root');
        $writableId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'writable',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);
        $readonlyId = $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'readonly',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        $writable = Inode::findOne($writableId);
        $readonly = Inode::findOne($readonlyId);
        $this->tester->amLoggedInAs(UserIdentity::USER_A);
        AccessControl::grantAccessToUsers($writable->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);
        AccessControl::grantAccessToUsers($readonly->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        $service = Yii::createObject(CutPasteService::class);

        $this->assertTrue($service->cutInodes([$writable, $readonly]));
        $this->assertSame([$writable->uuid], $service->getCutInodeUuids());
    }

    public function testPasteInodesMovesTheCutItemAndClearsSession(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $destination = $this->tester->grabFixture('inodes', 'dir');
        $this->tester->amLoggedInAs(UserIdentity::USER_A);

        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);
        AccessControl::grantAccessToUsers($destination->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        $service = Yii::createObject(CutPasteService::class);

        $this->assertTrue($service->cutInode($file));
        $this->assertTrue($service->pasteInodes($destination));

        $this->tester->seeRecord(Inode::class, [
            'id' => $file->id,
            'parent_id' => $destination->id,
        ]);
        $this->assertSame([], $service->getCutInodeUuids());
        $this->assertFalse($service->hasCutInodes());
    }

    public function testPasteInodesRejectsNonDirectoryDestinations(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_A);
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        $service = Yii::createObject(CutPasteService::class);
        $service->cutInode($file);

        $this->expectException(ForbiddenHttpException::class);
        $service->pasteInodes($file);
    }

    public function testPasteInodesRejectsDestinationsWithoutWritePermission(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $destination = $this->tester->grabFixture('inodes', 'dir');
        $this->tester->amLoggedInAs(UserIdentity::USER_A);

        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);
        AccessControl::grantAccessToUsers($destination->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        $service = Yii::createObject(CutPasteService::class);
        $service->cutInode($file);

        $this->expectException(ForbiddenHttpException::class);
        $service->pasteInodes($destination);
    }
}
