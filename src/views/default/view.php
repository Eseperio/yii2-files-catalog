<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\File */
/* @var $tag string */

/* @var $checkFilesIntegrity boolean */

use yii\helpers\Html;
use yii\helpers\Inflector;

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
                    <?= Inflector::camel2words($model->name) ?></h1>
            </div>
            <div class="panel-body">
                <?php if (!empty($tag)): ?>

                <?php else: ?>
                    <div class="alert alert-info">
                        <p><?= Yii::t('filescatalog', 'This file cannot be displayed online.') ?></p>
                        <p><?= Html::a(Yii::t('xenon', 'Download'), ['download', 'uuid' => $model->uuid], [
                                'class' => 'btn btn-default'
                            ]) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title"><?= Yii::t('xenon', 'Info') ?></div>
            </div>
            <div class="panel-body">
                <?php if (!empty($model->versions)): ?>

                <?php else: ?>
                    <p class="text-muted">
                        <?= Yii::t('xenon', 'This document has not versions') ?>
                    </p>
                <?php endif; ?>

                <?php if ($checkFilesIntegrity): ?>
                    <h3><?= Yii::t('xenon', 'Md5 Checksum') ?></h3>
                    <?= Html::tag('pre', $model->md5hash) ?>
                <?php endif; ?>
            </div>
        </div>


    </div>
</div>
