<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

class NewFolderAction extends Action
{
    use ModuleAwareTrait;

    public function run()
    {


        FileTypeIconsAsset::register($this->controller->view);
        $uuid = Yii::$app->request->get('uuid', false);
        $parent = Directory::find()->uuid($uuid)->one();

        if (empty($parent))
            throw new NotFoundHttpException('Page not found');

        $model = new Directory();

        if ($model->load(Yii::$app->request->post()) && $model->appendTo($parent)) {
            return $this->controller->redirect(['index', 'uuid' => $model->uuid]);
        }

        return $this->controller->render('new-folder', [
            'model' => $model,
            'parent' => $parent
        ]);

    }
}
