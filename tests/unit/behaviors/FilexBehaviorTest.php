<?php

namespace unit\behaviors;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\behaviors\FilexBehavior;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;
use yii\base\Component;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;

class FilexBehaviorTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();
    }

    public function testSetControlInfoPopulatesInsertAuditFields(): void
    {
        $this->tester->amLoggedInAs(UserIdentity::USER_A);

        $owner = new class extends Component {
            use ModuleAwareTrait;

            public $created_by;
            public $updated_by;
            public $created_at;
            public $updated_at;
            public $author_name;
            public $editor_name;
        };
        $behavior = new FilexBehavior();
        $behavior->attach($owner);

        $behavior->setControlInfo(new ModelEvent([
            'name' => ActiveRecord::EVENT_BEFORE_INSERT,
        ]));

        $this->assertSame(UserIdentity::USER_A, $owner->created_by);
        $this->assertSame('user1', $owner->author_name);
        $this->assertNotNull($owner->created_at);
    }

    public function testSetControlInfoPopulatesUpdateAuditFields(): void
    {
        $this->tester->amLoggedInAs(UserIdentity::USER_C);

        $owner = new class extends Component {
            use ModuleAwareTrait;

            public $created_by;
            public $updated_by;
            public $created_at;
            public $updated_at;
            public $author_name;
            public $editor_name;
        };
        $behavior = new FilexBehavior();
        $behavior->attach($owner);

        $behavior->setControlInfo(new ModelEvent([
            'name' => ActiveRecord::EVENT_BEFORE_UPDATE,
        ]));

        $this->assertSame(UserIdentity::USER_C, $owner->updated_by);
        $this->assertSame('user3', $owner->editor_name);
        $this->assertNotNull($owner->updated_at);
    }
}
