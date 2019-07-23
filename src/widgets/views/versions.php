<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

use yii\helpers\Html;

/* @var \eseperio\filescatalog\models\File[] $versions */
/* @var \eseperio\filescatalog\models\File $model */
?>
<?php if (!empty($versions)): ?>
    <ol class="filex-versions ">
        <?php foreach ($versions as $version): ?>
            <li>
                <?= Html::a($version->humanName, ['view', 'uuid' => $version->uuid]); ?>
            </li>
        <?php endforeach; ?>
    </ol>
<?php else: ?>
    <p class="text-muted">
        <?= Yii::t('filescatalog', 'This document has not versions') ?>
    </p>
<?php endif; ?>
<hr>
<?= \eseperio\filescatalog\widgets\Uploader::widget([
    'model' => $model,
]) ?>

