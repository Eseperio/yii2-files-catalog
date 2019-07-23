<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\Html;

/* @var \eseperio\filescatalog\models\File[] $versions */
/* @var \eseperio\filescatalog\models\File $model */
/* @var \eseperio\filescatalog\models\File $lastVersion */
/* @var boolean $isLast whether current model is last version */
?>
<?php if (!empty($versions)): ?>
    <h4><?= Yii::t('filescatalog', 'Versions') ?></h4>
    <?php if (!$isLast): ?>
        <div class="alert alert-warning">
            <i class="glyphicon glyphicon-warning-sign"></i>
            <?= Yii::t('xenon', 'This is an older version of the document') ?>.
            <?= Html::a(Yii::t('xenon','Go last'), ['view', 'uuid' => $lastVersion->uuid], [
                'class' => $model->id === $lastVersion->id ? "text-info" : ""
            ]); ?>
        </div>
    <?php endif; ?>
    <?php if ($model->type == InodeTypes::TYPE_VERSION): ?>
        <?= Html::a($model->original->getHumanName(30), ['view', 'uuid' => $model->original->uuid], [
            'class' => ''
        ]); ?>
    <?php else: ?>
        <?= Html::tag('strong', $model->humanName, [
            'class' => ''
        ]); ?>
    <?php endif; ?>

    <ol class="filex-versions ">
        <?php foreach ($versions as $version): ?>
            <li>
                <?php if ($model->id !== $version->id): ?>
                    <?= Html::a($version->getHumanName(30), ['view', 'uuid' => $version->uuid], [
                        'class' => $model->id === $version->id ? "text-info" : ""
                    ]); ?>

                <?php else: ?>
                    <?= Html::tag('strong', $version->humanName, [
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

