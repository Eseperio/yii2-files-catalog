<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

use eseperio\filescatalog\models\InodePermissionsForm;

/* @var $model \eseperio\filescatalog\models\Inode|\eseperio\filescatalog\models\File */
/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */


$fileRoles = $filexModule->getAclPermissions();
$fileRoles[InodePermissionsForm::CUSTOM_ROLE_VALUE] = Yii::t('filescatalog', 'Custom');

$dataProvider = new \yii\data\ActiveDataProvider([
    'query' => $model->getShares()
])
?>
<div class="panel">
    <div class="panel-heading">
        <div class="panel-title">
            <?= Yii::t('filescatalog', 'Shared with') ?>
        </div>
    </div>
    <div class="panel-body">
        <?= \eseperio\filescatalog\widgets\SharesGridview::widget([
            'dataProvider' => $dataProvider,
        ]) ?>
    </div>
    <div class="panel-footer">
        <?= \yii\helpers\Html::a(Yii::t('filescatalog', 'Share'), ['share', 'uuid' => $model->uuid], [
            'class' => 'btn btn-default'
        ]) ?>
    </div>
</div>
