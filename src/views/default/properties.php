<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\base\Inode */

/* @var $parent \eseperio\filescatalog\models\base\Inode */
/* @var $parentTreeNodes \eseperio\filescatalog\models\base\Inode[] */
/* @var $maxTreeDepth int */
/* @var $this \yii\web\View */
/* @var $childrenTreeNodes \eseperio\filescatalog\models\base\Inode[] */
/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */
/* @var $attributes array */

/* @var $accessControlFormModel InodePermissionsForm */


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\InodePermissionsForm;
use yii\helpers\Html;

FileTypeIconsAsset::register($this);

$canManageAcl = $filexModule->enableACL && $filexModule->isAdmin();

?>


<?= \eseperio\filescatalog\widgets\Breadcrumb::widget([
    'model' => $model,
    'showPropertiesBtn' => false
]) ?>
<div class="row">
    <div class="col-md-6 <?= $canManageAcl ? "" : "col-md-offset-3" ?>">
        <div class="panel">
            <div class="panel-body">
                <?php if ($model->type == InodeTypes::TYPE_VERSION): ?>
                    <div class="alert alert-warning">
                        <?= Yii::t('filescatalog', 'This are the properties of this file version.') ?>
                    </div>
                <?php endif; ?>
                <?= \yii\widgets\DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table'],
                    'attributes' => $attributes
                ]) ?>
            </div>
            <div class="panel-footer clearfix">

                <?php if (!empty($parent)): ?>
                    <?= Html::a(Yii::t('filescatalog', 'Open parent'), ['index', 'uuid' => $parent->uuid], ['class' => 'btn btn-default']) ?>
                <?php endif; ?>
                <?= Html::a(Yii::t('filescatalog', 'View contents'), [($model->type === InodeTypes::TYPE_DIR ? "index" : "view"), 'uuid' => $model->uuid], ['class' => 'btn btn-info pull-right ']) ?>
            </div>
        </div>
        <?php if (AclHelper::canDelete($model)): ?>
            <?php if ($model->type === InodeTypes::TYPE_VERSION): ?>

                <?= Html::a(Yii::t('filescatalog', 'Delete only this version'), ['delete', 'uuid' => $model->uuid], [
                    'class' => 'text-danger',
                    'data' => [
                        'method' => 'post',
                        'params' => [
                            $filexModule->secureHashParamName => $model->deleteHash
                        ],
                        'confirm' => Yii::t('filescatalog', 'Confirm deletion?')
                    ]
                ]) ?> |
            <?php endif; ?>
            <?= Html::a(Yii::t('filescatalog', 'Delete'), ['delete', 'uuid' => $model->uuid], [
                'class' => 'text-danger',
                'data' => [
                    'method' => 'post',
                    'params' => [
                        $filexModule->secureHashParamName => $model->deleteHash,
                        'delall' => true
                    ],
                    'confirm' => Yii::t('filescatalog', 'Confirm deletion?')
                ]
            ]) ?>
        <?php endif; ?>
    </div>
    <?php /** @var \eseperio\filescatalog\FilesCatalogModule $filexModule */
    if ($canManageAcl): ?>
        <div class="col-md-6">
            <?= $this->render('partials/_acl', [
                'accessControlFormModel' => $accessControlFormModel,
                'model' => $model
            ]) ?>
        </div>
    <?php endif; ?>
</div>
