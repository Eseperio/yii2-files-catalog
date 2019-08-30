<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\DynamicModel;

class BulkAcl extends Bulk
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController
     */
    public $controller;

    public function run()
    {
        $models = $this->getModels();
        $error = null;

        $formModel = new DynamicModel([
            'type',
            'value',
            'uuids',
            $this->module->secureHashParamName
        ]);
        $uuidLenght = 33;
        $formModel->addRule('uuids', 'each', ['rule' => ['string', 'min' => $uuidLenght, 'max' => $uuidLenght]]);


        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {

            var_dump('Ole');
            var_dump($formModel->uuids);
        }


        if ($this->checkSecureHash($models)) {
            if ($this->addPermissions($models, $permissions)) {
                var_dump($permissions);

                return $this->controller->goBack();
            } else {
                $error = Yii::t('filescatalog', 'An error ocurred when trying to delete');
            }
        }

        return $this->controller->render('bulk-acl', [
            'models' => $models,
            'error' => $error,
            'hash' => $this->getSecureHash($models),
            'formModel' => $formModel
        ]);
    }

    private function addPermissions(array $models, $permissions)
    {
        return true;
    }


}
