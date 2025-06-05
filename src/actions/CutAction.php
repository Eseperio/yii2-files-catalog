<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\services\CutPasteService;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class CutAction
 * Action for cutting a single inode
 * @package eseperio\filescatalog\actions
 */
class CutAction extends Action
{
    use ModuleAwareTrait;

    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;


    /**
     * Cut a single inode and store its UUID in the session
     * 
     * @param string $uuid UUID of the inode to cut
     * @param string|null $created_at Created at timestamp for symlinks
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function run($uuid, $created_at = null)
    {

        if (!$this->module->allowCutPaste) {
            throw new NotFoundHttpException();
        }

        // Find the inode
        $model = $this->controller->findModel($uuid, $created_at);
        if (empty($model)) {
            throw new NotFoundHttpException('Page not found');
        }

        // Get the cut paste service
        $service = Yii::createObject(CutPasteService::class);

        // Cut the inode
        if ($service->cutInode($model)) {
            Yii::$app->session->setFlash('success', Yii::t('filescatalog', 'Item has been cut. Navigate to the destination folder and click paste.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('filescatalog', 'An error occurred when trying to cut the item'));
        }

        // Redirect back to the original screen
        return $this->controller->goBack();
    }
}
