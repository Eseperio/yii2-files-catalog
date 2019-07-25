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
use yii\base\Action;
use yii\bootstrap\ActiveForm;
use yii\web\Controller;
use yii\web\Response;

class PropertiesAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {
        $model = $this->controller->findModel(Yii::$app->request->get('uuid', false));
        $permModel = new InodePermissionsForm();
        $permModel->inode_id = $model->id;
        if ($permModel->load(Yii::$app->request->post())) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($permModel);
            }
            if ($permModel->save()) {
                $permModel = new InodePermissionsForm();
                $permModel->inode_id = $model->id;

                $model->refresh();
            }
        }


        return $this->controller->render('properties', [
            'model' => $model,
            'accessControlFormModel' => $permModel
        ]);

    }
}
