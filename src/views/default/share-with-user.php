<?php


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use yii\helpers\Html;


/* @var $this \yii\web\View */
/* @var $model \eseperio\filescatalog\models\Inode */
/* @var $status int|bool */
/* @var $formModel \eseperio\filescatalog\models\InodeShare */

FileTypeIconsAsset::register($this);
$selector = Html::getInputId($formModel, 'set_expiring_date');
$this->registerJs(<<<JS
$(document).on('change','#{$selector}',function(e){
    console.log(this,e)
    $('.expiry-date').toggle($(this).is(':checked'))
});
JS
)
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <?php
        $form = \yii\widgets\ActiveForm::begin([

        ]);
        ?>
        <div class="panel">
            <div class="panel-heading">
                <p><span class="fiv-icon-folder fiv-sqo"></span>
                    <?= Html::a($model->parent->name, ['index', 'uuid' => $model->parent->uuid]) ?>
                </p>
            </div>
            <div class="panel-heading">
                <p class="panel-title">
                    <?= Yii::t('filescatalog', 'Sharing {filename} via email', [
                        'filename' => Html::tag('span', $model->publicName . "." . $model->extension, ['class' => 'text-info'])
                    ]) ?>
                </p>
            </div>

            <div class="panel-body">

                <?= $form->field($formModel, 'user_id')->label(Yii::t('filescatalog', 'Recipient')) ?>
                <?= $form->field($formModel, 'set_expiring_date')->checkbox(['label' => Yii::t('filescatalog', 'Set an expiry date')]) ?>
                <?= $form->field($formModel, 'expires_at', [
                    'options' => [
                        'class' => 'expiry-date '.($formModel->set_expiring_date?"":"collapse")
                    ]
                ])->input('date') ?>
            </div>
            <div class="panel-footer clearfix">
                <?= Html::a(Yii::t('filescatalog', 'Back'), \yii\helpers\Url::previous(), ['class' => 'btn btn-default']) ?>
                <button type="submit" class="btn btn-primary pull-right"><?= Yii::t('filescatalog', 'Share') ?></button>
            </div>
        </div>
        <?php
        \yii\widgets\ActiveForm::end();
        ?>
    </div>
</div>
