<?php


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use yii\helpers\Html;


/* @var $this \yii\web\View */
/* @var $model \eseperio\filescatalog\models\Inode */
/* @var $status int|bool */
/* @var $formModel \yii\base\DynamicModel */

FileTypeIconsAsset::register($this);

?>
<?php if ($status === false): ?>
    <div class="alert alert-danger">
        <?= Yii::t('filescatalog', 'An error ocurred sending the email. Check the address or contact the administrator') ?>
    </div>
<?php elseif ($status === true): ?>
    <div class="alert alert-success">
        <?= Yii::t('filescatalog', 'File shared') ?>
    </div>
<?php endif; ?>
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

                <?= $form->field($formModel, 'recipient')->label(Yii::t('filescatalog', 'Recipient')) ?>
                <?= $form->field($formModel, 'message')->textarea()->label(Yii::t('filescatalog', 'Message')) ?>

            </div>
            <div class="panel-footer clearfix">
                <?= Html::a(Yii::t('filescatalog', 'Back'), ['index', 'uuid' => $model->parent->uuid], ['class' => 'btn btn-default']) ?>
                <button type="submit" class="btn btn-primary pull-right"><?= Yii::t('filescatalog', 'Share') ?></button>
            </div>
        </div>
        <?php
        \yii\widgets\ActiveForm::end();
        ?>
    </div>
</div>
