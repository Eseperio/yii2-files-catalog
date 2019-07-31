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
        if ($this->checkSecureHash($models)) {
            if ($this->addPermissions($models, $permissions)) {
                return $this->controller->goBack();
            } else {
                $error = Yii::t('xenon', 'An error ocurred when trying to delete');
            }
        }

        return $this->controller->render('bulk-acl', [
            'models' => $models,
            'error' => $error,
            'hash'=>$this->getSecureHash($models)
        ]);
    }

    private function addPermissions(array $models, $permissions)
    {

    }


}
