<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\base\Inode */

/* @var $parent \eseperio\filescatalog\models\base\Inode */
/* @var $parentTreeNodes \eseperio\filescatalog\models\base\Inode[] */
/* @var $maxTreeDepth int */

/* @var $childrenTreeNodes \eseperio\filescatalog\models\base\Inode[] */

use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\helpers\Html;

FileTypeIconsAsset::register($this);
?>


<?= \eseperio\filescatalog\widgets\Breadcrumb::widget([
    'model' => $model,
    'showPropertiesBtn' => false
]) ?>
<div class="row">
    <div class="col-md-6 col-sm-offset-3">
        <div class="panel">

            <div class="panel-body">
                <?= \yii\widgets\DetailView::widget([
                    'model' => $model,
                    'options' => ['class' => 'table'],
                    'attributes' => [
                        'created_at:datetime',
                        'author_name',
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
                                'decimals'=>0

                            ]
                        ],
                        [
                            'attribute' => 'md5hash',
                            'visible' => Yii::$app->getModule('filex')->checkFilesIntegrity
                        ],
                        'mime',
                        'uuid',
//                        'realPath'
                    ]
                ]) ?>
            </div>
            <div class="panel-footer clearfix">
                <?php if (!empty($parent)): ?>
                    <?= Html::a(Yii::t('filescatalog', 'Open parent'), ['index', 'uuid' => $parent->uuid], ['class' => 'btn btn-default']) ?>
                <?php endif; ?>
                <?= Html::a(Yii::t('filescatalog', 'View contents'), ['index', 'uuid' => $model->uuid], ['class' => 'btn btn-info pull-right ']) ?>
            </div>
        </div>
    </div>
</div>
