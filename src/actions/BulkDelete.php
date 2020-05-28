<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use Yii;
use yii\web\Controller;

class BulkDelete extends Bulk
{
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {

        $models = $this->getModels();
        $error = null;
        if ($this->checkSecureHash($models)) {
            if ($this->deleteItems($models)) {
                return $this->controller->goBack();
            } else {
                $error = Yii::t('filescatalog', 'An error ocurred when trying to delete');
            }
        }

        return $this->controller->render('bulk-delete', [
            'models' => $models,
            'hash' => $this->getSecureHash($models),
            'error' => $error
        ]);

    }

    protected function getModels()
    {
        return $this->getModelsQuery()->onlyDeletable()->all();
    }

    /**
     * @param Inode[]| $models
     */
    private function deleteItems(array $models): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            foreach ($models as $model) {
                switch ($model->type) {
                    case InodeTypes::TYPE_DIR:
                        $descendantFiles = $model->getDescendants()->andWhere([
                            'type' => [InodeTypes::TYPE_VERSION, InodeTypes::TYPE_FILE, InodeTypes::TYPE_SYMLINK]
                        ]);
                        foreach ($descendantFiles->batch(50) as $rows) {
                            foreach ($rows as $row) {
                                /* @var $row Inode */
                                $row->delete();
                            }
                        }
                        $model->deleteWithChildren();
                        break;
                    case InodeTypes::TYPE_FILE:
                        $model->delete();
                        break;
                    case InodeTypes::TYPE_SYMLINK:
                        $model->delete();
                        break;
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            return false;
        }

        return true;
    }

}
