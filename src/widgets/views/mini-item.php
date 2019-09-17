<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */


/* @var $model \eseperio\filescatalog\models\Inode */

use app\helpers\Html;

?>
<p>
    <?= \eseperio\filescatalog\widgets\IconDisplay::widget([
        'model' => $model
    ]) ?>
    <?= Html::a(Html::encode($model->humanName), ['/filex/default/view', 'uuid' => $model->uuid], [
        'target' => '_blank',
        'data' => [
            'pjax' => 0
        ]
    ]); ?>
</p>

