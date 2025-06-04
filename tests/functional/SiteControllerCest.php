<?php

use app\controllers\SiteController;

class SiteControllerCest
{
    public function _before(FunctionalTester $I)
    {
        // No setup needed
    }

    public function testActionIndex(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->see('Test App');
    }

    public function testActionDirectoryTree(FunctionalTester $I)
    {
        $I->amOnPage('/index-test.php?r=site/directory-tree');
        $I->expectTo('see the directory tree widget test page');

        // First, check that the page loads and the navigation is visible
        $I->seeElement('.navbar-brand');
        $I->see('Test App');

        // Check if we can see any content on the page
        $I->comment('Checking page content');
        $I->see('Directory Tree Widget Test', 'h1');

        // Check that the form is displayed
        $I->seeElement('form');

        // Check for the widget container
        $I->seeElement('.directory-tree-container');

        // Test form submission with a sample value
        $I->fillField(['name' => 'DynamicModel[selection]'], 'test/path');
        $I->click('Submit');

        // Check that the success message is displayed
        $I->see('Selected: test/path');
    }
}
