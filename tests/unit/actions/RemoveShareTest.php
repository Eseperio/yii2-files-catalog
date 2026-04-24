<?php

namespace unit\actions;

use app\models\UserIdentity;
use Codeception\Test\Unit;
use eseperio\filescatalog\actions\RemoveShare;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeShare;
use tests\_fixtures\InodeFixture;
use UnitTester;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class RemoveShareTest extends Unit
{
    protected UnitTester $tester;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->truncateTable('fcatalog_shares')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_version')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes_perm')->execute();
        Yii::$app->db->createCommand()->truncateTable('fcatalog_inodes')->execute();

        Yii::$app->getModule('filex')->enableACL = true;
        Yii::$app->getModule('filex')->enableUserSharing = true;
    }

    public function testRunRemovesExistingShareAndRedirects(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $this->tester->amLoggedInAs(UserIdentity::USER_A);
        AccessControl::grantAccessToUsers($file->id, UserIdentity::USER_A, AccessControl::ACTION_WRITE);

        $share = new InodeShare();
        $share->inode_id = $file->id;
        $share->user_id = UserIdentity::USER_C;
        $share->expires_at = time() + 3600;
        $share->save(false);

        $controller = new class('test', Yii::$app) extends Controller {
            public Inode $inode;
            public $redirectUrl;

            public function findModel($uuid, $createdAt = null)
            {
                return $this->inode;
            }

            public function redirect($url, $statusCode = 302)
            {
                $this->redirectUrl = $url;
                return Yii::$app->response;
            }
        };
        $controller->inode = $file;

        Yii::$app->request->setBodyParams([
            'uuid' => $file->uuid,
            'user_id' => UserIdentity::USER_C,
        ]);

        $action = new RemoveShare('remove-share', $controller);
        $response = $action->run();

        $this->assertInstanceOf(\yii\web\Response::class, $response);
        $this->assertSame(['properties', 'uuid' => $file->uuid], $controller->redirectUrl);
        $this->assertNull(InodeShare::findOne([
            'inode_id' => $file->id,
            'user_id' => UserIdentity::USER_C,
        ]));
    }

    public function testRunRejectsWhenUserCannotShare(): void
    {
        $this->tester->haveFixtures([
            'inodes' => InodeFixture::class,
        ]);

        $file = $this->tester->grabFixture('inodes', 'file');
        $controller = new class('test', Yii::$app) extends Controller {
            public Inode $inode;

            public function findModel($uuid, $createdAt = null)
            {
                return $this->inode;
            }

            public function redirect($url, $statusCode = 302)
            {
                return Yii::$app->response;
            }
        };
        $controller->inode = $file;

        Yii::$app->request->setBodyParams([
            'uuid' => $file->uuid,
            'user_id' => UserIdentity::USER_C,
        ]);

        $action = new RemoveShare('remove-share', $controller);

        $this->expectException(ForbiddenHttpException::class);
        $action->run();
    }
}
