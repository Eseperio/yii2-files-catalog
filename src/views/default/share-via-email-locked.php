<?php


use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this \yii\web\View */
/* @var $model \eseperio\filescatalog\models\Inode */

/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel">
            <div class="panel-heading">
                <p class="panel-title">
                    <?= Yii::t('filescatalog', 'Sharing {filename} via email', [
                        'filename' => Html::tag('span', $model->publicName . "." . $model->extension, ['class' => 'text-info'])
                    ]) ?>
                </p>
            </div>

            <div class=" panel-body">
                <div class="alert alert-danger">
                    <?= Yii::t('filescatalog', 'This file is too big to be sent over email') ?>
                </div>
                <div>
                    <?= Yii::t('filescatalog', 'File weight is: {weight}', [
                        'weight' => Yii::$app->formatter->asShortSize($model->size, 0)
                    ]) ?>
                </div>
                <div> <?= Yii::t('filescatalog', 'Maximum allowed size is: {maxSize}', [
                        'maxSize' => Yii::$app->formatter->asShortSize($filexModule->maxFileSizeForEmailShare, 0)
                    ]) ?></div>
            </div>
            <div class="panel-footer">
                <?= Html::a(Yii::t('filescatalog', 'Back'), Url::previous(), ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>
</div>
