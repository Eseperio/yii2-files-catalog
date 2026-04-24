<?php

namespace unit\actions;

use Codeception\Test\Unit;
use eseperio\filescatalog\actions\PropertiesAction;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use UnitTester;
use Yii;
use yii\web\Controller;

class PropertiesActionTest extends Unit
{
    protected UnitTester $tester;

    public function testGetAttributesReturnsFileAttributesForFiles(): void
    {
        $action = new PropertiesAction('properties', new class extends Controller {
            public function __construct()
            {
                parent::__construct('test', Yii::$app);
            }
        });

        $inode = new Inode();
        $inode->type = InodeTypes::TYPE_FILE;

        $attributes = $action->getAttributes($inode);

        $this->assertSame('humanName:text', $attributes[0]);
        $this->assertSame('created_at:datetime', $attributes[1]);
        $this->assertSame('author_name', $attributes[2]);
        $this->assertTrue($attributes[3]['visible']);
        $file = new Inode();
        $file->type = InodeTypes::TYPE_FILE;
        $file->extension = 'txt';
        $this->assertStringContainsString('*.txt', $attributes[3]['value']($file));
    }

    public function testGetAttributesReturnsCommonAttributesForDirectories(): void
    {
        $action = new PropertiesAction('properties', new class extends Controller {
            public function __construct()
            {
                parent::__construct('test', Yii::$app);
            }
        });

        $inode = new Inode();
        $inode->type = InodeTypes::TYPE_DIR;

        $attributes = $action->getAttributes($inode);

        $this->assertSame('type', $attributes['type']['attribute']);
        $this->assertSame('created_at:datetime', $attributes[0]);
        $this->assertSame('author_name', $attributes[1]);
        $this->assertSame('uuid', $attributes[2]);
    }
}
