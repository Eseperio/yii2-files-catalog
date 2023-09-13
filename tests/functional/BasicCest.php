<?php

class BasicCest
{


    public function _before(FunctionalTester $I)
    {
        /* @var $module \eseperio\filescatalog\FilesCatalogModule */
        $module = Yii::$app->getModule('filex');
        $module->enableACL = false;

    }

    public function checkBaseTestAppWorks(FunctionalTester $I)
    {
        $I->amOnPage('/');
        $I->see('My Company');
    }

    // tests
    public function ensureRootIsCreatedIfMissing(FunctionalTester $I)
    {

        $I->amGoingTo('Clean all tables to test autogeneration of root folder');

        //cleanup db
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();

        $I->amOnRoute('filex/default/index');
        $I->see('root');
    }

    public function createSubdirectory(FunctionalTester $I)
    {

        $model = $I->grabRecord(\eseperio\filescatalog\models\Inode::class, ['name' => 'root']);
        $I->amOnRoute('filex/default/new-folder', ['uuid' => $model->uuid]);
        $I->amGoingTo('Test if access control is working for new folder form');
        $I->see('Page not found');
        $I->amLoggedInAs(100);
        $I->amOnRoute('filex/default/new-folder', ['uuid' => $model->uuid]);
        $I->see('root');
        $I->amGoingTo('send new folder form');
        $data = [
            yii\helpers\Html::getInputName($model, 'name') => 'test',
        ];
        $I->submitForm('.new-folder-form', $data);
        $I->seeRecord(\eseperio\filescatalog\models\Inode::class, ['name' => 'test']);

    }

    public function openNewDirectory(FunctionalTester $I)
    {
        $I->haveFixtures(['inodes' => \tests\_fixtures\InodeFixture::class]);
        $fixture = $I->grabFixture('inodes', 'dir');
        $I->amOnRoute('filex/default/index', ['uuid' => $fixture->uuid]);
    }






}
