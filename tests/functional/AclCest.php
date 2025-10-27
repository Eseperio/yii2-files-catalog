<?php

namespace functional;

use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\InodeShare;
use FunctionalTester;
use tests\_fixtures\InodeFixture;
use Yii;
use app\models\UserIdentity;

class AclCest
{
    /**
     * @var \eseperio\filescatalog\FilesCatalogModule
     */
    private $filexModule;

    public function _before(FunctionalTester $I)
    {
        $this->filexModule = Yii::$app->getModule('filex');
        $this->filexModule->enableACL = true;

    }

    public function checkAdminPermission(FunctionalTester $I)
    {
        $I->amGoingTo('Check if user with admin role can access all files');
        $this->filexModule->administratorPermissionName = 'adminPermission';

        $I->amLoggedInAs(UserIdentity::FILES_ADMINISTRATOR);
        $I->amOnRoute('filex/default/index');
        $I->see('root');
    }

    public function checkForbiddenWithoutPermission(FunctionalTester $I)
    {
        $I->amGoingTo('Check if access is denied to user when it has not assigned any of the admin permissions');
        $this->filexModule->administratorPermissionName = 'adminPermission';
        // Different user
        $I->amLoggedInAs(UserIdentity::USER_C);
        $I->amOnRoute('filex/default/index');
        $I->see('Forbidden');
    }

    public function checkIndividualPermissions(FunctionalTester $I)
    {
        $I->amGoingTo('Check user can access a file when it has been granted access to it');
        $I->amLoggedInAs(UserIdentity::USER_A);
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');

        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        $I->seeRecord(AccessControl::class, [
            'inode_id' => $fixture->id,
            'user_id' => UserIdentity::USER_A,
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
        $I->amLoggedInAs(UserIdentity::USER_A);

        AccessControl::grantAccessToRoles($fixture->id, AccessControl::LOGGED_IN_USERS, AccessControl::ACTION_READ);

        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);

        $I->see('Sample file');
    }

    public function checkExpiredShareDeniesAccess(FunctionalTester $I)
    {
        $I->wantTo('Check that access is denied when a share has expired');
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Grant access to user A via AccessControl (simulating a share)
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        // Create an expired share
        $share = new InodeShare();
        $share->inode_id = $fixture->id;
        $share->user_id = UserIdentity::USER_A;
        $share->expires_at = time() - 3600; // Expired 1 hour ago
        $share->save(false); // Skip validation to force expired date

        // Try to access the file - should be forbidden
        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);
        $I->see('Forbidden');
    }

    public function checkNonExpiredShareAllowsAccess(FunctionalTester $I)
    {
        $I->wantTo('Check that access is granted when a share has not expired');
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Grant access to user A via AccessControl (simulating a share)
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        // Create a non-expired share
        $share = new InodeShare();
        $share->inode_id = $fixture->id;
        $share->user_id = UserIdentity::USER_A;
        $share->expires_at = time() + 3600; // Expires in 1 hour
        $share->save(false); // Skip validation

        // Try to access the file - should be allowed
        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);
        $I->see('Sample file');
    }

    public function checkPermanentShareAllowsAccess(FunctionalTester $I)
    {
        $I->wantTo('Check that access is granted when a share has no expiration date');
        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);
        $fixture = $I->grabFixture('inodes', 'file');
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Grant access to user A via AccessControl (simulating a share)
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        // Create a permanent share (no expiration)
        $share = new InodeShare();
        $share->inode_id = $fixture->id;
        $share->user_id = UserIdentity::USER_A;
        $share->expires_at = null; // No expiration
        $share->save(false); // Skip validation

        // Try to access the file - should be allowed
        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);
        $I->see('Sample file');
    }
}
