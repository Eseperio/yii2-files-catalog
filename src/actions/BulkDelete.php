<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use app\helpers\ArrayHelper;
use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
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
        $error = null
        ;
        if ($this->checkSecureHash($models)) {
            if ($this->deleteItems($models)) {
                return $this->controller->goBack();
            } else {
                $error = Yii::t('xenon', 'An error ocurred when trying to delete');
            }
        }

        return $this->controller->render('bulk-delete', [
            'models' => $models,
            'hash' => $this->getSecureHash($models),
            'error' => $error
        ]);

    }


    /**
     * @param Inode[]|File[] $models
     */
    private function deleteItems(array $models): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            foreach ($models as $model) {
                switch ($model->type) {
                    case InodeTypes::TYPE_DIR:
                        $descendantFiles = $model->getDescendants()->andWhere([
                            'type' => [InodeTypes::TYPE_VERSION, InodeTypes::TYPE_FILE]
                        ]);
                        foreach ($descendantFiles->batch(50) as $rows) {
                            foreach ($rows as $row) {
                                /* @var $row File */
                                $row->delete();
                            }
                        }
                        $model->deleteWithChildren();
                        break;
                    case InodeTypes::TYPE_FILE:
                        $model->delete();
                        break;
                    case InodeTypes::TYPE_SYMLINK:
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