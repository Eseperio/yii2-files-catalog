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
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class DeleteAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {
        $uuid = Yii::$app->request->get('uuid');
        $model = $this->controller->findModel($uuid);

        if ($model->isRoot())
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'Root node can not be deleted'));

        $parentUuid = $model->getParent()->select('uuid')->scalar();

        $rcvdHash = Yii::$app->request->post($this->module->secureHashParamName);

        $createdAt = Yii::$app->request->post('created_at', false);

        if (!empty($createdAt) && strlen((string)$createdAt) == 10) {
            $model = Inode::find()->where([
                'created_at' => $createdAt,
                'uuid' => $uuid
            ])->one();

            if (empty($model))
                throw new NotFoundHttpException();
        }

        if (!empty($rcvdHash) && $rcvdHash === $model->deleteHash && AclHelper::canDelete($model)) {
            if ($model->type === InodeTypes::TYPE_DIR) {
                if (Yii::$app->request->post('confirm_text') === $model->getDeletionConfirmText()) {
                    //Delete files one per each
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
                } else {
                    return $this->controller->render('delete', [
                        'model' => $model
                    ]);
                }
            } else {
                if (Yii::$app->request->post('delall', false)) {
                    if ($model->type === InodeTypes::TYPE_VERSION) {
                        Inode::deleteAll([
                            'id' => ArrayHelper::getColumn($model->original->versions, 'id')
                        ]);
                        $model = $model->original;
                    }
                }
                $model->delete();
            }


            return $this->controller->redirect(['index', 'uuid' => $parentUuid]);
        } else {
            throw new BadRequestHttpException('Could not trust sender');
        }


    }

}
