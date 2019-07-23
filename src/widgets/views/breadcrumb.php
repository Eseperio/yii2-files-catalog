<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */


/* @var $this \yii\web\View */
/* @var $model \eseperio\filescatalog\models\base\Inode|\eseperio\filescatalog\models\Directory|\eseperio\filescatalog\models\File|\eseperio\filescatalog\models\Symlink */
/* @var $pjaxId string */
/* @var $showPropertiesBtn boolean */

/* @var $parents array */

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\Helper;
use eseperio\filescatalog\widgets\IconDisplay;
use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>


<div class="row">
    <div class="col-sm-8">
        <h2>
            <?php if (Yii::$app->controller->action->id == "properties"): ?>
                <?= Html::a(Yii::t('xenon', 'Back'), \yii\helpers\Url::previous(), ['class' => 'btn btn-default']) ?>
            <?php elseif (!empty($parents)): ?>
                <?php
                ?>
                <?= Html::a('..', ['index', 'uuid' => end($parents)['uuid']]) ?> /
            <?php endif; ?>
            <?= IconDisplay::widget([
                'model' => $model
            ]) ?>
            <?= $model->name ?></h2>
        <p class="text-muted"><?= join('/', ArrayHelper::map($parents, 'uuid', function ($item) {
                return Html::a(Helper::humanize($item['name']), ['index', 'uuid' => $item['uuid']]);
            })) ?></p>
    </div>
    <div class="col-sm-4 text-right">
        <?php if ($model->type == InodeTypes::TYPE_DIR): ?>

            <div class="h2">
                <div class="btn-group">
                    <?php
                    echo Html::a(Yii::t('filescatalog', 'New folder'), ['new-folder', 'uuid' => $model->uuid], ['class' => 'btn btn-default']);
                    if ($showPropertiesBtn)
                        echo Html::a(Yii::t('filescatalog', 'Properties'), ['properties', 'uuid' => $model->uuid], ['class' => 'btn btn-default'])
                    ?>

                    <?= Uploader::widget([
                        'targetUuid' => $model->uuid,
                        'pjaxId' => $pjaxId,
                    ]) ?>
                </div>
            </div>
        <?php endif; ?>
        <br>
        <div class="progress collapse" id="filex-progress">
            <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                 style="width: 0%;">
            </div>
        </div>

        <div id="filex-errors">

        </div>
    </div>
</div>
