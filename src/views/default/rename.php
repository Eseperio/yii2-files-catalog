<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\Inode */

/* @var $renameFormModel \yii\base\DynamicModel */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>


<div class="directory-form">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel">
                <div class="panel-header">
                    <h3><span class="fiv-icon-folder fiv-sqo"></span>
                        <?= Html::a($model->name, ['view', 'uuid' => $model->uuid]) ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(); ?>
                    <h3><?= Yii::t('filescatalog', 'New name') ?></h3>
                    <?= $form->field($renameFormModel, 'name')->textInput(['maxlength' => 256])->label(false) ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('filescatalog', 'Rename'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>


</div>
