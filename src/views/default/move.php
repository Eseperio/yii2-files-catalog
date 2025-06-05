<?php

use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\widgets\DirectoryTreeWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var Inode $model */
/** @var \yii\base\DynamicModel $moveFormModel */
/** @var string $rootNodeUuid */
/** @var array $excludedUuids */

$this->title = Yii::t('filescatalog', 'Move {0}', [$model->name]);

?>

<div class="row">
    <div class="col-md-12">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php $form = ActiveForm::begin(); ?>

        <div class="card">
            <div class="card-header">
                <?= Yii::t('filescatalog', 'Select destination folder') ?>
            </div>
            <div class="card-body">
                <?= DirectoryTreeWidget::widget([
                    'id' => 'destination-folder-id',
                    'model' => $moveFormModel,
                    'attribute' => 'destination_folder',
                    'mode' => DirectoryTreeWidget::MODE_DIRECTORIES_ONLY,
                    'rootNodeUuid' => $rootNodeUuid ?? null,
                    'excludedUuids' => $excludedUuids ?? [],
                ]) ?>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-offset-3 col-sm-6">
                        <?= Html::submitButton(Yii::t('filescatalog', 'Move'), ['class' => 'btn btn-success']) ?>
                        <?= Html::a(Yii::t('filescatalog', 'Cancel'), ['index', 'uuid' => $model->parent->uuid], ['class' => 'btn btn-default']) ?>
                    </div>
                </div>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
