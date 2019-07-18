<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
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
