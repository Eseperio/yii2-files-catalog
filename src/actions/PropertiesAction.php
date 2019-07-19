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

        $maxTreeDepth = $this->module->maxTreeDepthDisplay;
        $parentTreeNodes = $model->getParents($maxTreeDepth)
            ->orderAZ()
            ->asArray()
            ->all();

        $parent = $model->getParent()->one();

        $childrenTreeNodes = $model->getLeaves($maxTreeDepth)
            ->asArray()
            ->all();

        /* @todo: Check ACL */

        return $this->controller->render('properties', [
            'model' => $model,
            'parent' => $parent,
            'parentTreeNodes' => $parentTreeNodes,
            'childrenTreeNodes' => $childrenTreeNodes,
            'maxTreeDepth' => $maxTreeDepth
        ]);

    }
}
