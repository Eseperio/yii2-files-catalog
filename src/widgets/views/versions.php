<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\Html;

/* @var \eseperio\filescatalog\models\File[] $versions */
/* @var \eseperio\filescatalog\models\File $model */
?>
<?php if (!empty($versions)): ?>
    <ol class="filex-versions ">
        <?php foreach ($versions as $version): ?>
            <li>
                <?php if($model->id!==$version->id): ?>
                    <?= Html::a($version->humanName, ['view', 'uuid' => $version->uuid], [
                        'class' => $model->id === $version->id ? "text-info" : ""
                    ]); ?>

                <?php else: ?>
                    <?= Html::tag('strong',$version->humanName, [
                        'class' => ''
                    ]); ?>

                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php else: ?>
    <p class="text-muted">
        <?= Yii::t('filescatalog', 'This document has not versions') ?>
    </p>
<?php endif; ?>
<hr>
<?= Uploader::widget([
    'model' => $model,
]) ?>

