<?php

namespace unit\models;

use Codeception\Test\Unit;
use eseperio\filescatalog\models\InodePermissionsForm;
use UnitTester;

class InodePermissionsFormTest extends Unit
{
    protected UnitTester $tester;

    public function testBeforeValidateNormalizesUserType(): void
    {
        $model = new InodePermissionsForm();
        $model->scenario = InodePermissionsForm::SCENARIO_DEFAULT;
        $model->type = InodePermissionsForm::TYPE_USER;
        $model->inode_id = 10;
        $model->user_id = 42;
        $model->role = 'custom-role';

        $this->assertTrue($model->validate());
        $this->assertSame(InodePermissionsForm::DUMMY_ROLE, $model->role);
    }

    public function testBeforeValidateNormalizesRoleType(): void
    {
        $model = new InodePermissionsForm();
        $model->scenario = InodePermissionsForm::SCENARIO_DEFAULT;
        $model->type = InodePermissionsForm::TYPE_ROLE;
        $model->inode_id = 10;
        $model->role = 'team-editors';
        $model->user_id = 99;

        $this->assertTrue($model->validate());
        $this->assertSame(InodePermissionsForm::DUMMY_USER, $model->user_id);
    }

    public function testBeforeSaveExpandsCustomRoleValue(): void
    {
        $model = new InodePermissionsForm();
        $model->type = InodePermissionsForm::TYPE_ROLE;
        $model->role = InodePermissionsForm::CUSTOM_ROLE_VALUE;
        $model->custom_role = 'project-admin';

        $this->assertTrue($model->beforeSave(false));
        $this->assertSame('project-admin', $model->role);
    }
}
