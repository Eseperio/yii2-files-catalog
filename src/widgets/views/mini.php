<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */


/* @var $this \yii\web\View */

/* @var $dataProvider */

use eseperio\filescatalog\columns\IconColumn;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\Html;

?>

<div class="panel panel-default">
    <div class="panel-header">

        <div class="panel-title"><?= Yii::t('filescatalog', 'Files') ?></div>
    </div>
    <div class="panel-body">
        <?php
        \app\widgets\Pjax::begin([
            'id' => $pjaxId
        ])
        ?>

        <?php echo \eseperio\filescatalog\widgets\GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => IconColumn::class],
                'name',
                [
                    'content' => function ($model) {
                        return Html::a(Yii::t('filescatalog','View'), ['/filex/default/view', 'uuid' => $model->uuid], [
                            'target' => '_blank',
                            'class' => 'btn btn-sm btn-default pull-right',
                            'data' => [
                                'pjax' => 0
                            ]]);
                    }
                ]
            ]
        ])
        ?>


        <?php
        \app\widgets\Pjax::end()
        ?>
    </div>
    <div class="panel-footer">
        <?php if (AclHelper::canWrite($model)): ?>
            <?= Uploader::widget([
                'targetUuid' => $model->uuid,
                'pjaxId' => $pjaxId,

            ]) ?>
        <?php endif; ?>
    </div>
</div>

