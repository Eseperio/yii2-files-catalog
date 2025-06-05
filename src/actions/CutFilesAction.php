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

class CutFilesAction extends Action
{
    use ModuleAwareTrait;

    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    /**
     * Display cut files and handle paste operation
     * 
     * @param string|null $destination UUID of the destination directory for paste operation
     * @param bool $cancel Whether to cancel the cut operation
     * @param bool $confirm Whether to confirm the paste operation
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function run($destination = null, $cancel = false, $confirm = false)
    {
        if (!$this->module->allowCutPaste) {
            throw new NotFoundHttpException();
        }

        // Get the cut paste service
        $service = Yii::createObject(CutPasteService::class);

        // If cancel is true, clear cut inodes from session and redirect back
        if ($cancel) {
            $service->clearCutInodes();
            Yii::$app->session->setFlash('success', Yii::t('filescatalog', 'Cut operation has been cancelled'));
            return $this->controller->goBack();
        }

        // Get cut inodes
        $cutInodes = $service->getCutInodes();

        if (empty($cutInodes)) {
            return $this->controller->render('cut-files', [
                'cutInodes' => [],
                'destination' => null
            ]);
        }

        // If destination is provided, show the summary screen or perform paste operation if confirmed
        if ($destination !== null) {
            $destinationInode = $this->controller->findModel($destination);

            // Only perform paste operation if confirmed
            if ($confirm) {
                try {
                    if ($service->pasteInodes($destinationInode)) {
                        Yii::$app->session->setFlash('success', Yii::t('filescatalog', 'Items have been moved successfully'));
                        return $this->controller->redirect(['index', 'uuid' => $destinationInode->uuid]);
                    } else {
                        Yii::$app->session->setFlash('error', Yii::t('filescatalog', 'An error occurred when trying to paste the items'));
                    }
                } catch (ForbiddenHttpException $e) {
                    Yii::$app->session->setFlash('error', $e->getMessage());
                }
            }
        }

        return $this->controller->render('cut-files', [
            'cutInodes' => $cutInodes,
            'destination' => $destination ? $this->controller->findModel($destination) : null
        ]);
    }
}
