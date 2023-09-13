<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use yii\helpers\Html;
use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use eseperio\filescatalog\validators\UniqueFilenameOnFolderValidator;
use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\validators\StringValidator;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class RenameAction
 * @property DefaultController $controller
 * @package eseperio\filescatalog\actions
 */
class RenameAction extends Action
{
    use ModuleAwareTrait;

    public function run()
    {
        if (!$this->module->allowRenaming)
            throw new NotFoundHttpException();

        FileTypeIconsAsset::register($this->controller->view);
        $uuid = Yii::$app->request->getQueryParam('uuid', false);
        $model = $this->controller->findModel($uuid, Yii::$app->request->get('created_at'));


        if (empty($model))
            throw new NotFoundHttpException('Page not found');

        if (!AclHelper::canWrite($model))
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'You can not rename this item'));

        $trans = Yii::$app->db->beginTransaction();

        $renameFormModel = new DynamicModel([
            'name'
        ]);
        $renameFormModel->addRule('name', StringValidator::class, ['min' => 3, 'max' => 255]);
        $renameFormModel->addRule('name', UniqueFilenameOnFolderValidator::class, ['inode' => $model]);

        try {
            if ($renameFormModel->load(Yii::$app->request->post())) {
                if ($renameFormModel->validate()) {
                    $model->updateAttributes(['name' => $model->getSafeFileName(Html::encode($renameFormModel->name))]);
                    $trans->commit();

                    return $this->controller->goBack(['index', 'uuid' => $model->uuid]);
                }
            } else {
                $renameFormModel->name = $model->name;
            }
        } catch (\Throwable $e) {
            $trans->rollBack();
        }

        return $this->controller->render('rename', [
            'model' => $model,
            'renameFormModel' => $renameFormModel
        ]);

    }
}
