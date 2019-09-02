<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\Inode */

/* @var $parent \eseperio\filescatalog\models\Directory */

use yii\helpers\Html;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper; ?>

<?= IconDisplay::widget([
    'model' => $model
]) ?>

<?= Html::a(Html::encode(StringHelper::truncate($model->name,20)), ['view', 'uuid' => $model->uuid], ['target' => '_blank']) ?>
<?= Html::a(Yii::t('filescatalog', 'link this'), '#', [
    'class' => 'btn btn-primary pull-right',
    'data' => [
        'method' => 'post',
        'confirm' => Yii::t('filescatalog', 'Create a symlink to {file}?', ['file' => Html::encode($model->name)]),
        'params' => [
            'uuid' => $parent->uuid,
            'ruuid' => $model->uuid
        ]
    ]
]) ?>
<div class="text-muted">
    <?php
    $parents = ArrayHelper::getColumn($model->getParents(3)->asArray()->all(), 'name');
    echo implode("/", $parents)
    ?>
</div>
