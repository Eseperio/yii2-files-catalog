<?php
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

FileTypeIconsAsset::register($this);
?>


<?= \eseperio\filescatalog\widgets\Breadcrumb::widget([
    'model' => $model,
    'showPropertiesBtn' => false
]) ?>
<div class="row">
    <div class="col-md-6">
        <div class="panel">

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
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title">
                    <?= Yii::t('xenon','Hierarchy') ?>
                </div>
            </div>
            <div class="panel-body">
                <?= \eseperio\filescatalog\widgets\Tree::widget([
                    'nodes' => $model->parents()
                        ->andWhere(['>', 'lft', 1])
                        ->all()
                ]) ?>
            </div>
        </div>

    </div>
</div>
