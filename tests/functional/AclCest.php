<?php

namespace functional;

use eseperio\filescatalog\models\AccessControl;
use FunctionalTester;
use tests\_fixtures\InodeFixture;
use Yii;

class AclCest
{
    /**
     * @var \eseperio\filescatalog\FilesCatalogModule
     */
    private $filexModule;

    public function _before(FunctionalTester $I)
    {
        /* @var $module \eseperio\filescatalog\FilesCatalogModule */
        $this->filexModule = Yii::$app->getModule('filex');
        $this->filexModule->enableACL = true;

    }

    public function checkAdminPermission(FunctionalTester $I)
    {
        $I->wantTo('Check if user with admin role can access all files');
        $this->filexModule->administratorPermissionName = 'adminPermission';

        $I->amLoggedInAs(101);
        $I->amOnRoute('filex/default/index');
        $I->see('root');
    }

    public function checkForbiddenWithoutPermission(FunctionalTester $I)
    {
        $I->wantTo('Check if access is denied to user when it has not assigned any of the admin permissions');
        $this->filexModule->administratorPermissionName = 'adminPermission';
        // Different user
        $I->amLoggedInAs(100);
        $I->amOnRoute('filex/default/index');
        $I->see('Forbidden');
    }

    public function checkIndividualPermissions(FunctionalTester $I)
    {
        $I->wantTo('Check user can access a file when it has been granted access to it');
        $I->amLoggedInAs(100);
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');

        AccessControl::grantAccessToUsers($fixture->id, 100, AccessControl::ACTION_READ);

        $I->seeRecord(AccessControl::class, [
            'inode_id' => $fixture->id,
            'user_id' => 100,
            'crud_mask' => AccessControl::ACTION_READ
        ]);

        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);

        $I->see('Sample file');
    }

    public function checkPublicAccessToFile(FunctionalTester $I)
    {
        $I->wantTo('Check public access to a file if it has been granted');
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');

        AccessControl::grantAccessToUsers($fixture->id, AccessControl::WILDCARD_ROLE, AccessControl::ACTION_READ);

        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);

        $I->see('Sample file');
    }

    public function checkAccessToLoggedIn(FunctionalTester $I)
    {
        $I->wantTo('Check if logged in users can see a file if it has been granted through wildcard @ role');
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');
        $I->amLoggedInAs(100);

        AccessControl::grantAccessToUsers($fixture->id, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_READ);

        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);

        $I->see('Sample file');
    }





}
