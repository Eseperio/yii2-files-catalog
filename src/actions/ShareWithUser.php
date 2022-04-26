<?php

namespace eseperio\filescatalog\actions;

use eseperio\bootstrap\Html;
use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\validators\DateValidator;

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

        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            die($formModel->date);
            $this->share($formModel['user_id'], $formModel['date']);
        }

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
            $formModel = new DynamicModel(['user_id', 'date', 'set_end_date']);
            $formModel->addRule(['user_id'], 'integer');
            $formModel->addRule(['user_id'], 'required');
            $formModel->addRule(['set_end_date'], 'boolean');
            $setEndDateInputId = Html::getInputId($formModel, 'set_end_date');
            $formModel->addRule(['date'], 'required', [
                'when' => function ($model) {
                    return (bool)$model['set_end_date'];
                },
                'whenClient' => <<<JS
function(){
    return $('#{$setEndDateInputId}').is(':checked')
}
JS
            ]);
            $formModel->addRule(['date'], 'date', [
                'type' => DateValidator::TYPE_DATE,
                'format' => 'yyyy-MM-dd',
                'timestampAttribute' => 'date'
            ]);
            $this->formModelInstance = $formModel;
        }

        return $this->formModelInstance;
    }

    public function share($userId, $endDate)
    {
    }
}
