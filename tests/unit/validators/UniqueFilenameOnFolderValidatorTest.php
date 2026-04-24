<?php

namespace unit\validators;

use Codeception\Test\Unit;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\validators\UniqueFilenameOnFolderValidator;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

class UniqueFilenameOnFolderValidatorTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();
        Yii::$app->getModule('filex')->enableACL = false;
    }

    public function testInitRequiresAnInode(): void
    {
        $this->expectException(InvalidConfigException::class);

        $validator = new UniqueFilenameOnFolderValidator();
        $validator->init();
    }

    public function testValidateAttributeRejectsDuplicateSiblingNames(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $inode = $this->tester->grabFixture('inodes', 'file');
        $this->tester->haveInDatabase(Inode::tableName(), [
            'name' => 'Duplicated file name',
            'type' => $inode->type,
            'parent_id' => $inode->parent_id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        $model = new DynamicModel(['name' => 'Duplicated file name']);
        $validator = new UniqueFilenameOnFolderValidator([
            'inode' => $inode,
        ]);

        $validator->validateAttribute($model, 'name');

        $this->assertTrue($model->hasErrors('name'));
        $this->assertStringContainsString('already exists', $model->getFirstError('name'));
    }

    public function testValidateAttributeAllowsUniqueNames(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $inode = $this->tester->grabFixture('inodes', 'file');
        $model = new DynamicModel(['name' => 'Unique file name']);
        $validator = new UniqueFilenameOnFolderValidator([
            'inode' => $inode,
        ]);

        $validator->validateAttribute($model, 'name');

        $this->assertFalse($model->hasErrors('name'));
    }
}
