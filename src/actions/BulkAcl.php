<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\models\InodePermissionsForm;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\DynamicModel;
use yii\bootstrap\ActiveForm;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class BulkAcl extends Bulk
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController
     */
    public $controller;

    public function run()
    {
        throw new NotFoundHttpException();
        $models = $this->getModels();
        $error = null;

        $permModel = Yii::createObject(InodePermissionsForm::class);
        $filexModule = self::getModule();

        $formModel= new DynamicModel(['uuids']);
        if ($permModel->load(Yii::$app->request->post()) && $filexModule->enableACL && $filexModule->isAdmin()) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                return ActiveForm::validate($permModel);
            }
        }



        if ($this->checkSecureHash($models)) {
            if ($this->addPermissions($models, $permModel)) {
                return $this->controller->goBack();
            } else {
                $error = Yii::t('filescatalog', 'An error ocurred when trying to delete');
            }
        }

        return $this->controller->render('bulk-acl', [
            'models' => $models,
            'error' => $error,
            'hash' => $this->getSecureHash($models),
            'formModel' => $formModel,
            'accessControlFormModel'=> $permModel
        ]);
    }

    private function addPermissions(array $models, $permissions)
    {
        return true;
    }


}
