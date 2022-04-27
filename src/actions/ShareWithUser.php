<?php

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\models\base\InodeShare;
use Yii;
use yii\base\Action;
use yii\base\DynamicModel;

/**
 * @property \eseperio\filescatalog\controllers\DefaultController $controller
 */
class ShareWithUser extends Action
{
    /**
     * @var DynamicModel buffer for dynamic model
     */
    private $formModelInstance;

    /**
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function run()
    {
        $formModel = new InodeShare();
        $model = $this->controller->findModel(\Yii::$app->request->get('uuid'));
        $formModel->inode_id = $model->id;
        if ($formModel->load(Yii::$app->request->post()) && $formModel->save()) {
            return $this->controller->redirect(['view', 'uuid' => $model->uuid]);
        }

        // Prevent publishing real inode id
        $formModel->inode_id = null;

        return $this->controller->render('share-with-user', [
            'model' => $model,
            'formModel' => $formModel
        ]);
    }


}
