<?php

namespace functional;

use app\models\UserIdentity;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use FunctionalTester;
use tests\_fixtures\InodeDynamicFixture;
use tests\_fixtures\InodeFixture;
use Yii;
use yii\helpers\Url;

class CutPasteCest
{
    /**
     * @var \eseperio\filescatalog\FilesCatalogModule
     */
    private $filexModule;

    public function _before(FunctionalTester $I)
    {
        /* @var $module \eseperio\filescatalog\FilesCatalogModule */
        $this->filexModule = Yii::$app->getModule('filex');

        // Ensure cut and paste is enabled
        $this->filexModule->allowCutPaste = true;

        // Load fixtures
        $I->haveFixtures([
            'inodes' => InodeFixture::class,
            'inodeDynamic' => InodeDynamicFixture::class,
        ]);
    }

    // Test cutting a single file
    public function testCutSingleFile(FunctionalTester $I)
    {
        $I->wantTo('Test cutting a single file');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::FILES_ADMINISTRATOR);

        // Get the file fixture
        $file = $I->grabFixture('inodes', 'file');

        // Grant write permissions to the user
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        // Go to the file view page
        $I->amOnRoute('/filex/default/view', ['uuid' => $file->uuid]);

        // See the file name
        $I->see('Sample file');

        // Click the cut button (using AJAX POST request since it's not a form submission)
        $I->sendAjaxPostRequest('/filex/default/cut', ['uuid' => $file->uuid]);

        // We can't check flash messages directly in functional tests
        // Instead, we'll verify the session contains the cut inodes
        $I->amOnRoute('/filex/default/cut-files');
        $I->see('Items that has been cut');
    }

    // Test pasting a cut file
    public function testPasteCutFile(FunctionalTester $I)
    {
        $I->amGoingTo('Test pasting a cut file to a different directory');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::FILES_ADMINISTRATOR);
        $I->amOnRoute('/filex/default/index');
        $I->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        // Get the file and directory fixtures
        $file = $I->grabFixture('inodes', 'file');
        $dir = $I->grabFixture('inodes', 'dir');

        // Grant write permissions to the user for both file and directory
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE | AccessControl::ACTION_READ);
        AccessControl::grantAccessToUsers($dir->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE | AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_A);
        // Cut the file
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut', 'uuid' => $file->uuid]));
        $I->seeResponseCodeIsRedirection();

        // Go to the cut-files page with the destination parameter
        $I->amOnRoute('/filex/default/cut-files', ['destination' => $dir->uuid]);

        // See the confirmation page
        $I->see('Move this items to: Sample directory');
        $I->see('Sample file');

        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut-files', 'destination' => $dir->uuid, 'confirm' => 1]));
        $I->seeResponseCodeIsRedirection();

        // We can't check flash messages directly in functional tests
        // Instead, we'll verify the record has been updated in the database
        $I->seeRecord(Inode::class, [
            'uuid' => $file->uuid,
            'parent_id' => $dir->id
        ]);
    }

    // Test bulk cutting of multiple files
    public function testBulkCut(FunctionalTester $I)
    {
        $I->wantTo('Test bulk cutting of multiple files');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Create additional test files in the root directory
        $root = $I->grabFixture('inodes', 'root');

        // Create two new files
        $file1 = new Inode([
            'name' => 'Test file 1',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'created_at' => time(),
            'author_name' => 'Test User'
        ]);
        $file1->save();

        $file2 = new Inode([
            'name' => 'Test file 2',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => Yii::$app->security->generateRandomString(32),
            'created_at' => time(),
            'author_name' => 'Test User'
        ]);
        $file2->save();

        // Grant write permissions to the user for both files
        AccessControl::grantAccessToUsers($file1->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);
        AccessControl::grantAccessToUsers($file2->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);


        $I->sendAjaxPostRequest(Url::to(['/filex/default/bulk-cut']), [
            'uuids' => [$file1->uuid, $file2->uuid]
        ]);

        // We can't check flash messages directly in functional tests
        // Instead, we'll verify the session contains the cut inodes
        $I->amOnRoute('/filex/default/cut-files');
        $I->see('Items that has been cut');
    }

    // Test pasting bulk cut files
    public function testPasteBulkCutFiles(FunctionalTester $I)
    {
        $I->amGoingTo('Test pasting bulk cut files to a different directory');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::USER_A);
        $I->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        // Get the directory fixture
        $dir = $I->grabFixture('inodes', 'dir');

        // Create additional test files in the root directory
        $root = $I->grabFixture('inodes', 'root');

        // Crear dos archivos nuevos usando haveInDatabase
        $file1Uuid = Yii::$app->security->generateRandomString(32);
        $file2Uuid = Yii::$app->security->generateRandomString(32);

        $I->haveInDatabase(Inode::tableName(), [
            'name' => 'Bulk test file 1',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => $file1Uuid,
            'created_at' => time(),
            'author_name' => 'Test User'
        ]);

        $I->haveInDatabase(Inode::tableName(), [
            'name' => 'Bulk test file 2',
            'type' => InodeTypes::TYPE_FILE,
            'parent_id' => $root->id,
            'uuid' => $file2Uuid,
            'created_at' => time(),
            'author_name' => 'Test User'
        ]);

        $file1 = Inode::findOne(['uuid' => $file1Uuid]);
        $file2 = Inode::findOne(['uuid' => $file2Uuid]);

        // Grant write permissions to the user for both files and the directory
        $mask = AccessControl::ACTION_WRITE | AccessControl::ACTION_READ;
        AccessControl::grantAccessToUsers($file1->id, UserIdentity::USER_A, $mask);
        AccessControl::grantAccessToUsers($file2->id, UserIdentity::USER_A, $mask);
        AccessControl::grantAccessToUsers($dir->id, UserIdentity::USER_A, $mask);

        // Perform bulk cut operation
        $I->sendAjaxPostRequest(Url::to(['/filex/default/bulk-cut']), [
            'uuids' => [$file1->uuid, $file2->uuid]
        ]);
        // Go to the cut-files page with destination parameter
        $I->amOnRoute('/filex/default/cut-files', ['destination' => $dir->uuid]);

        // Confirm the paste operation
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut-files', 'destination' => $dir->uuid, 'confirm' => 1]));
        $I->seeResponseCodeIsRedirection();

        // We can't check flash messages directly in functional tests
        // Instead, we'll verify the records have been updated in the database
        $I->seeRecord(Inode::class, [
            'uuid' => $file1->uuid,
            'parent_id' => $dir->id
        ]);

        $I->seeRecord(Inode::class, [
            'uuid' => $file2->uuid,
            'parent_id' => $dir->id
        ]);
    }

    // Test cutting without write permissions
    public function testCutWithoutWritePermissions(FunctionalTester $I)
    {
        $I->wantTo('Test cutting a file without write permissions');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Get the file fixture
        $file = $I->grabFixture('inodes', 'file');

        // Grant only read permissions to the user (not write)
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        // Try to cut the file - this should fail with a 403 error
        // Instead of checking the response content, we'll just verify that the session doesn't contain cut inodes
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut']), ['uuid' => $file->uuid]);

        // Go to the cut-files page to verify no items were cut
        $I->amOnRoute('/filex/default/cut-files');
        $I->see('No items have been cut');
    }

    public function testPasteWithoutWritePermissions(FunctionalTester $I)
    {
        $I->amGoingTo('Test pasting to a directory without write permissions');
        // Login as a user
        $I->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);
        $I->amLoggedInAs(UserIdentity::FILES_ADMINISTRATOR);

        // Get the file and directory fixtures
        $file = $I->grabFixture('inodes', 'file');
        $dir = $I->grabFixture('inodes', 'dir');

        // Grant write permissions to the user for the file but only read for the directory
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE | AccessControl::ACTION_READ);

        AccessControl::removeAccessToUser($dir->id, UserIdentity::USER_A);
        AccessControl::grantAccessToUsers($dir->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        $I->amLoggedInAs(UserIdentity::USER_A);
        // Cut the file
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut', 'uuid' => $file->uuid]));

        $I->seeResponseCodeIsRedirection();
        // Go to the cut-files page with destination parameter
        $I->amOnRoute('/filex/default/cut-files', ['destination' => $dir->uuid]);

        // Should see a message about not having write permissions
        $I->see('You do not have write permissions for this directory');

        // Try to confirm the paste operation
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut-files', 'destination' => $dir->uuid, 'confirm' => 1]));
        // We can't check flash messages directly in functional tests
        // Instead, we'll verify the record has not been moved
        $I->seeRecord(Inode::class, [
            'uuid' => $file->uuid,
            'parent_id' => $file->parent_id // Original parent ID
        ]);
    }

    // Test canceling a cut operation
    public function testCancelCutOperation(FunctionalTester $I)
    {
        $I->wantTo('Test canceling a cut operation');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Get the file fixture
        $file = $I->grabFixture('inodes', 'file');

        // Grant write permissions to the user
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        // Cut the file
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut', 'uuid' => $file->uuid]));
        // Cancel the cut operation
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut-files', 'cancel' => 1]));
        // We can't check flash messages directly in functional tests
        // Instead, we'll verify the cut-files page shows no items have been cut

        // Go to the cut-files page
        $I->amOnRoute('/filex/default/cut-files');

        // Should see a message about no items being cut
        $I->see('No items have been cut');
    }

    // Test paste button in breadcrumb
    public function testPasteButtonInBreadcrumb(FunctionalTester $I)
    {
        $I->wantTo('Test that paste button appears in breadcrumb when items are cut');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Get the file and directory fixtures
        $file = $I->grabFixture('inodes', 'file');
        $dir = $I->grabFixture('inodes', 'dir');

        // Grant write permissions to the user for both file and directory
        $mask = AccessControl::ACTION_WRITE | AccessControl::ACTION_READ;
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, $mask);
        AccessControl::grantAccessToUsers($dir->id, UserIdentity::USER_A, $mask);

        // Cut the file
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut', 'uuid' => $file->uuid]));
        // Go to the directory view page
        $I->amOnRoute('/filex/default/index', ['uuid' => $dir->uuid]);

        // Should see the paste button with a red dot
        $I->seeElement('a[href*="cut-files"][href*="destination"]');
        $I->seeElement('span[style*="color: red"]');
    }

    // Test disabled paste button when no write permissions
    public function testDisabledPasteButtonWithoutWritePermissions(FunctionalTester $I)
    {
        $I->amGoingTo('Test that paste button is disabled when user has no write permissions');

        // Login as a user
        $I->amLoggedInAs(UserIdentity::USER_A);

        // Get the file and directory fixtures
        $file = $I->grabFixture('inodes', 'file');
        $dir = $I->grabFixture('inodes', 'dir');

        // Grant write permissions to the user for the file but only read for the directory
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE | AccessControl::ACTION_READ);
        AccessControl::grantAccessToUsers($dir->id, UserIdentity::USER_A, AccessControl::ACTION_READ);

        // Cut the file
        $I->sendAjaxPostRequest(Url::to(['/filex/default/cut', 'uuid' => $file->uuid]));

        // Go to the directory view page
        $I->amOnRoute('/filex/default/index', ['uuid' => $dir->uuid]);

        // Should see the paste button with a grey dot and disabled class
        $I->seeElement('a.disabled');
        $I->seeElement('span[style*="color: grey"]');
    }
}
