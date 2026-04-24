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
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();
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
        $I->amLoggedInAs(UserIdentity::USER_C);

        // Grant access to user C via AccessControl (simulating a share)
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        // Persist an expired share directly so the model hooks do not delete it immediately.
        $I->haveInDatabase('fcatalog_shares', [
            'inode_id' => $fixture->id,
            'user_id' => UserIdentity::USER_C,
            'expires_at' => time() - 3600, // Expired 1 hour ago
        ]);

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
        $I->amLoggedInAs(UserIdentity::USER_C);

        // Grant access to user A via AccessControl (simulating a share)
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        // Create a non-expired share
        $share = new InodeShare();
        $share->inode_id = $fixture->id;
        $share->user_id = UserIdentity::USER_C;
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
        $I->amLoggedInAs(UserIdentity::USER_C);

        // Grant access to user A via AccessControl (simulating a share)
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        // Create a permanent share (no expiration)
        $share = new InodeShare();
        $share->inode_id = $fixture->id;
        $share->user_id = UserIdentity::USER_C;
        $share->expires_at = null; // No expiration
        $share->save(false); // Skip validation

        // Try to access the file - should be allowed
        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);
        $I->see('Sample file');
    }

    /**
     * Verify a user who only has access to the container can see the container
     * but does not leak sibling items in the listing.
     */
    public function checkIndexDoesNotLeakUnreadableChildren(FunctionalTester $I)
    {
        $I->wantTo('Confirm the index only shows children the current user can read');

        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);

        $root = $I->grabFixture('inodes', 'root');
        AccessControl::grantAccessToUsers($root->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_C);
        $I->amOnRoute('filex/default/index', ['uuid' => $root->uuid]);

        $I->see('root');
        $I->dontSee('Sample file');
        $I->dontSee('Sample directory');
    }

    /**
     * Verify private items cannot be opened nor downloaded by another user.
     */
    public function checkPrivateFileCannotBeViewedOrDownloadedWithoutAcl(FunctionalTester $I)
    {
        $I->wantTo('Confirm that a private file is not accessible through view or download routes');

        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);

        $fixture = $I->grabFixture('inodes', 'file');
        $I->amLoggedInAs(UserIdentity::USER_C);

        $I->amOnRoute('filex/default/view', ['uuid' => $fixture->uuid]);
        $I->see('Forbidden');

        $I->amOnPage(Url::to(['/filex/default/download', 'uuid' => $fixture->uuid]));
        $I->see('Forbidden');
    }

    /**
     * Verify read-only ACL grants do not unlock write actions or ACL editing.
     */
    public function checkReadOnlyAclDoesNotAllowMutatingActions(FunctionalTester $I)
    {
        $I->wantTo('Confirm that a read-only grant allows viewing but blocks mutating actions');

        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);

        $fixture = $I->grabFixture('inodes', 'file');
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_C);

        $I->amOnRoute('filex/default/properties', ['uuid' => $fixture->uuid]);
        $I->see('Sample file');
        $I->dontSee('Access control');

        $I->amOnPage(Url::to(['/filex/default/rename', 'uuid' => $fixture->uuid]));
        $I->see('Forbidden');

        $I->amOnPage(Url::to(['/filex/default/move', 'uuid' => $fixture->uuid]));
        $I->see('Forbidden');
    }

    /**
     * Verify delete rejects users that can read but cannot delete.
     */
    public function checkDeleteRequiresDeletePermission(FunctionalTester $I)
    {
        $I->wantTo('Confirm that delete is blocked when the user does not have delete permission');

        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);

        $fixture = $I->grabFixture('inodes', 'file');
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_C);

        $I->sendAjaxPostRequest(Url::to(['/filex/default/delete', 'uuid' => $fixture->uuid]), [
            'fxsh' => $fixture->deleteHash,
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeRecord(Inode::class, [
            'uuid' => $fixture->uuid,
        ]);
    }

    /**
     * Verify bulk delete only acts on items the current user can delete.
     */
    public function checkBulkDeleteFiltersOutNonDeletableItems(FunctionalTester $I)
    {
        $I->wantTo('Confirm that bulk delete does not delete items without delete permission');

        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);

        $root = $I->grabFixture('inodes', 'root');
        $deletableUuid = Yii::$app->security->generateRandomString(32);
        $readOnlyUuid = Yii::$app->security->generateRandomString(32);

        $deletableId = $I->haveInDatabase(Inode::tableName(), [
            'name' => 'bulk-delete-allowed',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => $deletableUuid,
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);
        $readOnlyId = $I->haveInDatabase(Inode::tableName(), [
            'name' => 'bulk-delete-denied',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => $readOnlyUuid,
            'depth' => 1,
            'filesize' => 0,
            'created_at' => time(),
            'author_name' => 'System',
        ]);

        AccessControl::grantAccessToUsers($deletableId, UserIdentity::USER_C, AccessControl::ACTION_READ | AccessControl::ACTION_DELETE);
        AccessControl::grantAccessToUsers($readOnlyId, UserIdentity::USER_C, AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_C);
        $I->sendAjaxPostRequest(Url::to(['/filex/default/bulk-delete']), [
            'uuids' => [$deletableUuid, $readOnlyUuid],
        ]);
        $I->seeResponseCodeIs(200);
        $source = $I->grabPageSource();
        $I->assertStringContainsString('bulk-delete-allowed', $source);
        $I->assertStringNotContainsString('bulk-delete-denied', $source);

        $hash = hash('sha3-256', (string)$deletableId . $this->filexModule->salt);
        $I->sendAjaxPostRequest(Url::to(['/filex/default/bulk-delete']), [
            'uuids' => [$deletableUuid, $readOnlyUuid],
            'fxsh' => $hash,
            'confirm_text' => mb_substr($hash, 0, 5),
        ]);

        $I->seeResponseCodeIsRedirection();
        $I->dontSeeRecord(Inode::class, [
            'uuid' => $deletableUuid,
        ]);
        $I->seeRecord(Inode::class, [
            'uuid' => $readOnlyUuid,
        ]);
    }

    /**
     * Verify user sharing routes enforce canShare on the server side.
     */
    public function checkShareActionsRequireSharePermission(FunctionalTester $I)
    {
        $I->wantTo('Confirm that share routes are forbidden when the user cannot share');

        $this->filexModule->enableUserSharing = true;
        $this->filexModule->enableEmailSharing = true;

        $I->haveFixtures([
            'inodes' => InodeFixture::class
        ]);

        $fixture = $I->grabFixture('inodes', 'file');
        AccessControl::grantAccessToUsers($fixture->id, UserIdentity::USER_C, AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_C);

        $I->amOnPage(Url::to(['/filex/default/share', 'uuid' => $fixture->uuid]));
        $I->see('Forbidden');

        $I->amOnPage(Url::to(['/filex/default/email', 'uuid' => $fixture->uuid]));
        $I->see('Forbidden');
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
     * Verify that copyPermissionToDescendants() replaces descendant permissions
     * for the same user/role, regardless of their previous crud_mask.
     */
    public function testCopyPermissionToDescendantsOverridesSamePrincipalAndKeepsOthers(FunctionalTester $I)
    {
        $I->wantTo('Verify copyPermissionToDescendants copies the parent mask and leaves other principals untouched');

        $I->haveFixtures(['inodes' => InodeFixture::class]);
        $dir = $I->grabFixture('inodes', 'dir');

        $childAId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'inherit_child_read_only',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);
        $childBId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'inherit_child_read_write',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);
        $childCId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'inherit_child_other_user',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);

        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childCId,
            'user_id'   => UserIdentity::USER_C,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);

        $aclRecord = AccessControl::findOne([
            'inode_id' => $dir->id,
            'user_id'  => UserIdentity::USER_A,
            'role'     => AccessControl::DUMMY_ROLE,
        ]);
        $aclRecord->copyPermissionToDescendants();

        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->dontSeeRecord(AccessControl::class, [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childCId,
            'user_id'   => UserIdentity::USER_C,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
    }

    /**
     * Verify the inherit-acl HTTP action copies the parent permission to all descendants.
     */
    public function testInheritAclActionCopiesParentPermissionToDescendants(FunctionalTester $I)
    {
        $I->wantTo('Apply ACL inheritance through the inherit-acl action');

        $this->filexModule->administratorPermissionName = 'adminPermission';
        $I->amLoggedInAs(UserIdentity::FILES_ADMINISTRATOR);
        $I->haveFixtures(['inodes' => InodeFixture::class]);
        $dir = $I->grabFixture('inodes', 'dir');

        $childAId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'action_inherit_child_read_only',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);
        $childBId = $I->haveInDatabase(Inode::tableName(), [
            'name'        => 'action_inherit_child_read_write',
            'type'        => InodeTypes::TYPE_FILE,
            'parent_id'   => $dir->id,
            'uuid'        => Uuid::uuid4()->toString(),
            'depth'       => 2,
            'filesize'    => 0,
            'created_at'  => time(),
            'author_name' => 'System',
        ]);

        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->haveInDatabase(AccessControl::tableName(), [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ | AccessControl::ACTION_WRITE,
        ]);

        $I->sendAjaxPostRequest(Url::to(['/filex/default/inherit-acl']), [
            'inode_id'  => $dir->id,
            'user_id'   => UserIdentity::USER_A,
            'role'      => AccessControl::DUMMY_ROLE,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);

        $I->seeResponseCodeIsRedirection();

        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childAId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->seeRecord(AccessControl::class, [
            'inode_id'  => $childBId,
            'user_id'   => UserIdentity::USER_A,
            'crud_mask' => AccessControl::ACTION_READ,
        ]);
        $I->dontSeeRecord(AccessControl::class, [
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
