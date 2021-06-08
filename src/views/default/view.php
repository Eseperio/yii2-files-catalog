<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\File */
/* @var $tag string */
/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */
/* @var $previous \eseperio\filescatalog\models\Inode|null previous inode */
/* @var $next \eseperio\filescatalog\models\Inode|null next inode */
/* @var $nextLink string|array link to next item */
/* @var $prevLink string|array link to previous item */
/* @var $checkFilesIntegrity boolean */

use eseperio\filescatalog\widgets\Versions;
use yii\helpers\Html;

?>
<?= \eseperio\filescatalog\widgets\Breadcrumb::widget([
    'model' => $model
]) ?>

<div class="row">
    <div class="col-md-8">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">
                    <span class="fiv-sqo fiv-icon-<?= Html::encode($model->extension) ?>"></span>
                    <?= $model->humanName ?></h1>
            </div>
            <div class="panel-body">
                <?php if ($tag !== false): ?>
                    <?php if (!empty($tag)): ?>
                        <?= $tag ?>
                    <?php else: ?>
                        <div class=" text-center">
                            <p><?= Yii::t('filescatalog', 'This file cannot be displayed online.') ?></p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <?= Yii::t('filescatalog', 'File does not exists') ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="panel-footer">
                <div class="text-center">
                    <p><?= Html::a(Yii::t('filescatalog', 'Download'), ['download', 'uuid' => $model->uuid], [
                            'class' => 'btn btn-default'
                        ]) ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title"><?= Yii::t('filescatalog', 'Info') ?></div>
            </div>
            <?php if ($filexModule->allowVersioning): ?>

                <div class="panel-body ">
                    <?= Versions::widget([
                        'model' => $model
                    ]) ?>
                </div>
                <hr>
            <?php endif; ?>

            <div class="panel-body">
                <?= Html::a(Yii::t('filescatalog', 'View file properties'), ['properties', 'uuid' => $model->uuid, 'original' => true], ['class' => 'btn btn-primary']) ?>
            </div>
            <hr>
            <div class="panel-body">
                <h1><?= Yii::$app->request->get('filex-offset') ?></h1>
                <?php if (!empty($prevLink)): ?>
                    <?= Html::a(Yii::t('filescatalog', 'Previous'), $prevLink, ['class' => 'btn btn-primary','data-pjax'=>0]) ?>
                <?php endif; ?>
                <?php if (!empty($nextLink)): ?>
                    <?= Html::a(Yii::t('filescatalog', 'Next'), $nextLink, ['class' => 'btn btn-primary pull-right','data-pjax'=>0]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
