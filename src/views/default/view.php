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


/* @var $model \eseperio\filescatalog\models\File */

use eseperio\filescatalog\assets\FileTypeIconsAsset;
use yii\helpers\Html;
use yii\helpers\Inflector;

var_dump($model->type)
?>


<h1>
    <span class="fiv-sqo fiv-icon-<?= Html::encode($model->extension) ?>"></span>

    <?= Inflector::camel2words($model->name) ?></h1>

<div class="row">
    <div class="col-md-6">
    </div>
</div>
