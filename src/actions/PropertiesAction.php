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
use yii\web\Controller;

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
        $permissionsModel = new InodePermissionsForm();
        if ($permissionsModel->load(Yii::$app->request->post()))
            $permissionsModel->save();


        return $this->controller->render('properties', [
            'model' => $model,
            'accessControlFormModel' => $permissionsModel
        ]);

    }
}
