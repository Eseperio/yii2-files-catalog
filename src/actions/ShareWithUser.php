<?php

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\models\InodeShare;
use eseperio\filescatalog\traits\ModuleAwareTrait;
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
        $shareModel = Yii::createObject(InodeShare::class);
        $model = $this->controller->findModel(\Yii::$app->request->get('uuid'));
        $shareModel->inode_id = $model->id;
        if ($shareModel->load(Yii::$app->request->post()) && $shareModel->save()) {
            return $this->controller->redirect(['view', 'uuid' => $model->uuid]);
        }

        // Prevent publishing real inode id
        $shareModel->inode_id = null;
        if($shareModel->isNewRecord){
            $shareModel->expires_at= Yii::$app->formatter->asDate('+1day','yyyy-MM-dd');
        }

        return $this->controller->render('share-with-user', [
            'model' => $model,
            'formModel' => $shareModel
        ]);
    }


}
