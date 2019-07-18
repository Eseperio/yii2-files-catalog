<?php
/**
 * Copyright (c) 2019. Grupo Smart (Spain)
 *
 * This software is protected under Spanish law. Any distribution of this software
 * will be prosecuted.
 *
 * Developed by Waizabú <code@waizabu.com>
 * Updated by: erosdelalamo on 18/7/2019
 *
 *
 */


/* @var $this \yii\web\View */
/* @var $model \eseperio\filescatalog\models\base\Inode|\eseperio\filescatalog\models\Directory|\eseperio\filescatalog\models\File|\eseperio\filescatalog\models\Symlink */

/* @var $parents array */

use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

?>


<div class="row">
    <div class="col-sm-8">
        <h2>
            <span class="fiv-sqo fiv-icon-folder"></span>
            <?= $model->name ?></h2>
        <p class="text-muted"><?= join('/', ArrayHelper::map($parents, 'uuid', function ($item) {
                return Html::a(Inflector::camel2words($item['name']), ['index', 'uuid' => $item['uuid']]);
            })) ?></p>
    </div>
    <div class="col-sm-4 text-right">
        <div class="h2">
            <?= Html::a(Yii::t('filescatalog', 'Add folder'), ['new-folder', 'uuid' => $model->uuid], ['class' => 'btn btn-primary']) ?>

            <?= Uploader::widget(['targetUuid' => $model->uuid,
                'pjaxId' => $pjaxId]) ?>

        </div>
    </div>
</div>
