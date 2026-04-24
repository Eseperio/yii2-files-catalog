<?php

namespace unit\models;

use Codeception\Test\Unit;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use Ramsey\Uuid\Uuid;
use UnitTester;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\StringHelper;

class InodeTest extends Unit
{
    protected UnitTester $tester;

    public function testGetSafeFileNameNormalizesStrings(): void
    {
        $inode = new Inode();

        $this->assertSame('my_sample_file', $inode->getSafeFileName('My Sample File'));
    }

    public function testSetAsVersionChangesTypeAndUuid(): void
    {
        $inode = new Inode();
        $inode->setAsVersion('uuid-original');

        $this->assertSame('uuid-original', $inode->uuid);
        $this->assertSame(InodeTypes::TYPE_VERSION, $inode->type);
    }

    public function testGetPublicNameUsesOriginalForVersions(): void
    {
        $original = new Inode();
        $original->name = 'Original File';

        $version = new Inode();
        $version->type = InodeTypes::TYPE_VERSION;
        $version->populateRelation('original', $original);

        $this->assertSame('Original File', $version->getPublicName());
    }

    public function testGetDeleteHashUsesModuleSaltAndIdentity(): void
    {
        $module = Yii::$app->getModule('filex');
        $module->salt = 'unit-test-salt';

        $inode = new Inode();
        $inode->id = 99;
        $inode->uuid = 'uuid-for-hash';

        $this->assertSame(
            hash('SHA3-256', '99deleteunit-test-saltuuid-for-hash'),
            $inode->getDeleteHash()
        );
    }

    public function testSetStreamRejectsDirectoriesAndAcceptsFiles(): void
    {
        $dir = new Inode();
        $dir->type = InodeTypes::TYPE_DIR;

        $this->expectException(InvalidArgumentException::class);
        $dir->setStream(fopen('php://temp', 'r+'));
    }

    public function testSetStreamReturnsTheSameInstanceForFiles(): void
    {
        $file = new Inode();
        $file->type = InodeTypes::TYPE_FILE;
        $stream = fopen('php://temp', 'r+');

        $this->assertSame($file, $file->setStream($stream));
        fclose($stream);
    }
}
