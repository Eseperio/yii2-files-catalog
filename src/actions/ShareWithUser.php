<?php

namespace eseperio\filescatalog\actions;

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
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function run()
    {
        $formModel = $this->getFormModel();
        $model = $this->controller->findModel(\Yii::$app->request->get('uuid'));
        return $this->controller->render('share-with-user', [
            'model' => $model,
            'formModel' => $formModel
        ]);
    }

    /**
     * @return \yii\base\DynamicModel
     */
    public function getFormModel()
    {
        if (empty($this->formModelInstance)) {
            $formModel = new DynamicModel(['recipient', 'message']);
            $formModel->addRule(['recipient'], 'email');
            $formModel->addRule(['message'], 'string', ['max' => 256]);
            $this->formModelInstance = $formModel;
        }

        return $this->formModelInstance;
    }
}
