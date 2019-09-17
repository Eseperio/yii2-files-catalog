<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\Html;

/* @var \eseperio\filescatalog\models\Inode[] $versions */
/* @var \eseperio\filescatalog\models\Inode $model */
/* @var \eseperio\filescatalog\models\Inode $lastVersion */
/* @var boolean $isLast whether current model is last version */
/* @var boolean $isVersion whether current model version */

?>
<?php if (!empty($versions)): ?>
    <h4><?= Yii::t('filescatalog', 'Versions') ?></h4>
    <?php if (!$isLast): ?>
        <div class="alert alert-warning">
            <?= Yii::t('filescatalog', 'This is an older version of the document') ?>.
            <?= Html::a(Yii::t('filescatalog', 'Go last'), ['view', 'uuid' => $lastVersion->uuid]); ?>
        </div>
    <?php endif; ?>
    <?php if ($isVersion): ?>
        <?php
        $name = $model->original->getHumanName(30);
        $name .= Html::tag('span', "(" . Yii::t('filescatalog', 'Original') . ")", ['class' => 'text-muted']);
        echo Html::a($name, ['view', 'uuid' => $model->original->uuid, 'original' => true], [
            'class' => ''
        ]); ?>
    <?php else: ?>
        <?= Html::tag('strong', $model->getHumanName(30)); ?>
    <?php endif; ?>

    <ol class="filex-versions" style="max-height: 300px; overflow: auto">
        <?php foreach ($versions as $version): ?>
            <li>
                <?php if ($model->id !== $version->id): ?>
                    <?= Html::a($version->getHumanName(30), ['view', 'uuid' => $version->uuid], [
                        'class' => $model->id === $version->id ? "text-info" : ""
                    ]); ?>

                <?php else: ?>
                    <?= Html::tag('strong', $version->getHumanName(30)); ?>
                    <?= Html::tag('span', "(" . Yii::$app->formatter->asDate($version->created_at)
                        . " "
                        . Yii::t('filescatalog', 'by')
                        . " "
                        . Html::encode($model->author_name)
                        . ")", ['class' => 'text-muted']) ?>
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
    'model' => !$isVersion ? $model : $model->original,
]) ?>

