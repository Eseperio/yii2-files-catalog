<?php

class ModuleLoadingCest
{
    public function _before(FunctionalTester $I)
    {
        // No setup needed
    }

    // Test that the module is properly loaded
    public function checkModuleLoading(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->expectTo('see that the module is loaded');
        $I->seeElement('.navbar-brand');
        $I->see('Test App');
        $I->see('Home');
        $I->comment('Module is properly loaded');
    }
}
