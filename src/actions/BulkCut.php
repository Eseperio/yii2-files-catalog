<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\services\CutPasteService;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class BulkCut extends Bulk
{
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {
        if (!$this->module->allowCutPaste) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'Cut and paste is not allowed'));
        }

        $models = $this->getModels();

        // Get the cut paste service
        $service = Yii::createObject(CutPasteService::class);

        // Directly cut items without showing confirmation screen
        if ($service->cutInodes($models)) {
            Yii::$app->session->setFlash('success', Yii::t('filescatalog', 'Items have been cut. Navigate to the destination folder and click paste.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('filescatalog', 'An error occurred when trying to cut the selected items'));
        }

        // Redirect back to the original screen
        return $this->controller->goBack();
    }

    /**
     * Get only models that the user has write permissions for
     * @return array|Inode[]|\yii\db\ActiveRecord[]
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    protected function getModels()
    {
        $models = parent::getModels();

        // Filter models to only include those the user has write permissions for
        $writableModels = [];
        $nonWritableCount = 0;

        foreach ($models as $model) {
            if (AclHelper::canWrite($model)) {
                $writableModels[] = $model;
            } else {
                $nonWritableCount++;
            }
        }

        // If any models don't have write permissions, show an error
        if ($nonWritableCount > 0) {
            Yii::$app->session->setFlash('error', Yii::t('filescatalog', 'You do not have write permissions for {count} of the selected items. Cut operation cannot proceed.', [
                'count' => $nonWritableCount
            ]));
            return $writableModels;
        }

        return $writableModels;
    }
}
