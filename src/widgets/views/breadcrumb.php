<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */
/* @var bool $showLabels */

/* @var \eseperio\filescatalog\FilesCatalogModule $filexModule */
/* @var $this \yii\web\View */
/* @var $model \eseperio\filescatalog\models\Inode|\eseperio\filescatalog\models\Directory|\eseperio\filescatalog\models\File|\eseperio\filescatalog\models\Symlink */
/* @var $pjaxId string */
/* @var $showPropertiesBtn boolean */
/* @var string $newFolderIcon */
/* @var string $newFolderLabel */
/* @var $parents array */
/* @var $propertiesLabel string */
/* @var $linkLabel string */
/* @var $linkIcon string */

/* @var string $propertiesIcon */

use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\helpers\Helper;
use eseperio\filescatalog\widgets\IconDisplay;
use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\StringHelper;
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
            endif; ?>


            <?= IconDisplay::widget([
                'model' => $model
            ]) ?>
            <span data-toggle="tooltip"
                  title="<?= Html::encode($model->publicName) ?>"><?= StringHelper::truncate($model->publicName, 12) ?></span>
            <?php if ($filexModule->enableUserSharing && $model->shared): ?>
                <span><?= Yii::t('filescatalog', 'Shared with {qty}', ['qty' => $model->shared]) ?></span>
            <?php endif; ?>

            <?php
            if ($model->type === InodeTypes::TYPE_VERSION): ?>
                <small data-toggle="tooltip"
                       title="<?= Yii::t('filescatalog', 'Original') . ": " . $model->name ?>">
                    <?= StringHelper::truncate($model->name, 12) ?>
                </small>
            <?php endif; ?>
        </h1>
        <p class="text-muted">
            <?php if (!empty($parents) && AclHelper::canRead(end($parents))): ?>
            <?php $pieces = ArrayHelper::map($parents, 'uuid', function ($item) {
                if (AclHelper::canRead($item))
                    return Html::a(Helper::humanize($item['name']), ['index', 'uuid' => $item['uuid']]);

                return null;
            });
            $pieces = array_filter($pieces);
            echo join('/', $pieces) ?></p>
        <?php endif; ?>
    </div>
    <div class="col-sm-5 text-right">
        <?php if ($model->type == InodeTypes::TYPE_DIR): ?>
            <div class="h1">
                <div class="btn-group">
                    <?php
                    if (AclHelper::canWrite($model)) {

                        echo Html::a($newFolderIcon . " " . ($showLabels ? $newFolderLabel : ""), ['new-folder', 'uuid' => $model->uuid], [
                            'class' => 'btn btn-default',
                            'title' => $newFolderLabel,
                            'data' => [
                                'toggle' => 'tooltip',
                                'pjax' => 0,
                                'container' => 'body'
                            ]
                        ]);
                        echo Html::a($linkIcon . " " . ($showLabels ? $linkLabel : ""), ['new-link', 'uuid' => $model->uuid], [
                            'class' => 'btn btn-default',
                            'title' => $linkLabel,
                            'data' => [
                                'toggle' => 'tooltip',
                                'pjax' => 0,
                                'container' => 'body'
                            ]
                        ]);
                    }
                    if ($showPropertiesBtn)
                        echo Html::a($propertiesIcon . " " . ($showLabels ? $propertiesLabel : ""), ['properties', 'uuid' => $model->uuid], [
                            'class' => 'btn btn-default',
                            'title' => $propertiesLabel,
                            'data' => [
                                'toggle' => 'tooltip',
                                'pjax' => 0,
                                'container' => 'body'
                            ],


                        ])
                    ?>
                    <?php if (AclHelper::canWrite($model)): ?>
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
