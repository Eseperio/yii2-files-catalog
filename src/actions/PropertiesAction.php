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
        $parent = $model->parents(1)->one();

        $parentTreeNodes = $model->parents()
            ->andWhere(['>', 'lft', 1])
            ->orderAZ()
            ->all();


        $childrenTreeNodes = $model->children(4)->all();

        /* @todo: Check ACL */

        return $this->controller->render('properties', [
            'model' => $model,
            'parent' => $parent,
            'parentTreeNodes' => $parentTreeNodes,
            'childrenTreeNodes' => $childrenTreeNodes
        ]);

    }
}
