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


/* @var $model \eseperio\filescatalog\models\base\Inode */

/* @var $parent \eseperio\filescatalog\models\base\Inode */

use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\helpers\Html;
use yii\helpers\Inflector;

FileTypeIconsAsset::register($this);
?>


<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel">
            <div class="panel-heading">
                <h1 class="panel-title">
                    <span class="fiv-sqo fiv-icon-<?= ($model->type == InodeTypes::TYPE_DIR ? 'folder' : Html::encode($model->extension)) ?>">

                    </span>
                    <?= Inflector::camel2words($model->name) ?></h1>
            </div>
            <div class="panel-body">
                <?= \yii\widgets\DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'created_at:datetime',
                        'created_by',
                        [
                            'attribute' => 'extension',
                            'format' => 'raw',
                            'visible' => $model->type === InodeTypes::TYPE_FILE,
                            'value' => function ($model) {
                                $html = IconDisplay::widget([
                                    'model' => $model,
                                    'iconSize' => IconDisplay::SIZE_MD
                                ]);

                                if ($model->type === InodeTypes::TYPE_FILE) {
                                    $html .= " *." . Html::encode($model->extension);
                                }

                                return $html;
                            }
                        ],
                        [
                            'attribute' => 'filesize',
                            'format' => [
                                'shortSize',
                                0
                            ]
                        ],
                        [
                            'attribute' => 'md5hash',
                            'visible' => Yii::$app->getModule('filex')->checkFilesIntegrity
                        ],
                        'mime',
                        'uuid',
                        'realPath'
                    ]
                ]) ?>
            </div>
            <div class="panel-footer">
                <?php if (!empty($parent)): ?>
                    <?= Html::a(Yii::t('filescatalog', 'Back to index'), ['index', 'uuid' => $parent->uuid], ['class' => 'btn btn-info']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
