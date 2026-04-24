<?php

namespace functional;

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeShare;
use FunctionalTester;
use Ramsey\Uuid\Uuid;
use tests\_fixtures\InodeFixture;
use Yii;
use yii\helpers\Url;
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

    // -----------------------------------------------------------------------
    // Tests for remove-acl-from-descendants
    // -----------------------------------------------------------------------

    /**
     * Verify that removeExactPermissionFromDescendants() only removes ACL records
     * whose crud_mask matches exactly, leaving records with a different mask intact.
     */
    public function testRemoveExactPermissionFromDescendantsOnlyDeletesMatchingMask(FunctionalTester $I)
    {
        $I->wantTo('Verify removeExactPermissionFromDescendants removes only descendants with the exact crud_mask');

        $I->haveFixtures(['inodes' => InodeFixture::class]);
        $dir = $I->grabFixture('inodes', 'dir'); // id=2, depth=1, parent=root

        // Create two child files under dir
        $childAId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'child_read_only',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);
        $childBId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'child_read_write',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);

        // Parent dir: READ grant for user_A (this is the grant we propagate from)
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        // child_A: READ-only grant – should be deleted by the call
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        // child_B: READ|WRITE grant – must be preserved (different mask)
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);

        // Invoke the model method directly on the parent dir's READ grant
        $aclRecord = AccessControl::findOne([
            'inode_id' => $dir->id,
            'user_id'  => UserIdentity::USER_A,
            'role'     => AccessControl::DUMMY_ROLE,
        ]);
        $aclRecord->removeExactPermissionFromDescendants();

        // child_A's READ grant must be gone
        $I->dontSeeRecord(AccessControl::class, [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        // child_B's READ|WRITE grant must still be there
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);
        // Parent dir's own READ grant must be untouched
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
    }

    /**
     * Verify the remove-acl-from-descendants HTTP action removes the exact-mask
     * grant from descendants and redirects, while leaving other masks intact.
     */
    public function testRemoveAclFromDescendantsActionDeletesExactMatch(FunctionalTester $I)
    {
        $I->wantTo('Remove exact ACL permission from descendants via the remove-acl-from-descendants action');

        $this->filexModule->administratorPermissionName = 'adminPermission';
        $I->amLoggedInAs(UserIdentity::FILES_ADMINISTRATOR);
        $I->haveFixtures(['inodes' => InodeFixture::class]);
        $dir = $I->grabFixture('inodes', 'dir');

        // Create two child files under dir
        $childAId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'action_child_read',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);
        $childBId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'action_child_rw',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);

        // Parent dir: READ grant (the action will look this up via inode_id+user_id+role+crud_mask)
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        // child_A: READ grant – should be deleted
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        // child_B: READ|WRITE grant – must be preserved
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);

        $I->sendAjaxPostRequest(Url::to(['/filex/default/remove-acl-from-descendants']), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);

        $I->seeResponseCodeIsRedirection();

        // child_A's READ grant must be deleted
        $I->dontSeeRecord(AccessControl::class, [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        // child_B's READ|WRITE grant must be preserved
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);
    }

    /**
     * Verify that non-admin users cannot call remove-acl-from-descendants.
     */
    public function testRemoveAclFromDescendantsActionRequiresAdmin(FunctionalTester $I)
    {
        $I->wantTo('Verify that remove-acl-from-descendants action is forbidden for non-admin users');

        $this->filexModule->administratorPermissionName = 'adminPermission';
        $I->amLoggedInAs(UserIdentity::USER_C); // has no adminPermission
        $I->haveFixtures(['inodes' => InodeFixture::class]);
        $dir = $I->grabFixture('inodes', 'dir');

        $I->sendAjaxPostRequest(Url::to(['/filex/default/remove-acl-from-descendants']), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);

        $I->seeResponseCodeIs(403);
    }
}
