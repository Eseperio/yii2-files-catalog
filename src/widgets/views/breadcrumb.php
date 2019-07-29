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

use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\helpers\Helper;
use eseperio\filescatalog\widgets\IconDisplay;
use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

FileTypeIconsAsset::register($this);
?>


<div class="row">
    <div class="col-sm-7">
        <h1>
            <?php if (Yii::$app->controller->action->id == "properties"): ?>
                <?= Html::a(Yii::t('filescatalog', 'Back'), Url::previous(), [
                    'class' => 'btn btn-default'
                ]);
            elseif (!empty($parents)):
                $parent = end($parents);
                if (AclHelper::canRead($parent))
                    echo Html::a('..', ['index', 'uuid' => $parent['uuid']]) . "/";
            endif;
            if ($model->type === InodeTypes::TYPE_VERSION): ?>
                <small class=""
                       title="<?= Yii::t('filescatalog', 'Original') . ": " . $model->original->humanName ?>"><?= $model->original->getHumanName(12) ?></small>
            <?php endif; ?>
            <?= IconDisplay::widget([
                'model' => $model
            ]) ?>
            <span title="<?= $model->humanName ?>"><?= $model->getHumanName(23) ?></span>
        </h1>
        <p class="text-muted">
            <?php if (!empty($parents) && AclHelper::canRead(end($parents))): ?>
            <?= join('/', ArrayHelper::map($parents, 'uuid', function ($item) {
                return Html::a(Helper::humanize($item['name']), ['index', 'uuid' => $item['uuid']]);
            })) ?></p>
        <?php endif; ?>

    </div>
    <div class="col-sm-5 text-right">
        <?php if ($model->type == InodeTypes::TYPE_DIR): ?>

            <div class="h1">
                <div class="btn-group">
                    <?php
                    if (AclHelper::cantWrite($model))
                        echo Html::a(Yii::t('filescatalog', 'New folder'), ['new-folder', 'uuid' => $model->uuid], ['class' => 'btn btn-default']);
                    if ($showPropertiesBtn)
                        echo Html::a(Yii::t('filescatalog', 'Properties'), ['properties', 'uuid' => $model->uuid], ['class' => 'btn btn-default'])
                    ?>
                    <?php if (AclHelper::cantWrite($model)): ?>
                        <?= Uploader::widget([
                            'targetUuid' => $model->uuid,
                            'pjaxId' => $pjaxId,
                        ]) ?>
                    <?php endif; ?>

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
