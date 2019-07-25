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

/* @var $accessControlFormModel InodePermissionsForm */

use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\InodePermissionsForm;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\bootstrap\ActiveForm;
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
                                'decimals' => 0

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
    <div class="col-md-6">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title">
                    <?= Yii::t('filescatalog', 'Access control') ?>
                </div>
            </div>
            <div class="panel-body">
                <?php
                $form = ActiveForm::begin([
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                    'id' => 'contact-form',

                ]);
                echo $form->errorSummary($accessControlFormModel);
                echo $form->field($accessControlFormModel, 'inode_id')->hiddenInput()->label(false);
                echo $form->field($accessControlFormModel, 'type')->radioList([
                    InodePermissionsForm::TYPE_USER => Yii::t('filescatalog', 'User'),
                    InodePermissionsForm::TYPE_ROLE => Yii::t('filescatalog', 'Role'),
                ]);
                ?>
                <div class="row">
                    <div class="col-sm-12">
                        <?= $form->field($accessControlFormModel, 'user_id')->textInput(['type' => 'number']);
                        ?>
                    </div>
                    <div class="col-sm-12">
                        <?= $form->field($accessControlFormModel, 'role', [
                            'options' => ['class' => 'form-group collapse']
                        ])->textInput();
                        ?>
                    </div>
                </div>
                <?php
                echo $form->field($accessControlFormModel, 'crud')->checkboxList([
                    AccessControl::ACTION_CREATE => Yii::t('filescatalog', 'Create'),
                    AccessControl::ACTION_READ => Yii::t('filescatalog', 'Read'),
                    AccessControl::ACTION_UPDATE => Yii::t('filescatalog', 'Update'),
                    AccessControl::ACTION_DELETE => Yii::t('filescatalog', 'Delete')
                ]);
                echo Html::submitButton(Yii::t('filescatalog', 'Add permission'), ['class' => 'btn btn-primary']);
                ActiveForm::end();
                ?>
            </div>
            <div class="panel-heading">
                <div class="panel-title"><?= Yii::t('filescatalog', 'Current permissions') ?></div>
            </div>
            <div class="panel-body">
                <ul class="list-group"><?php
                    foreach ($model->accessControlList as $item) :?>
                        <li class="list-group-item">
                            <div class="row">

                                <div class="col-sm-4">
                                    <?php if ($item->role !== AccessControl::DUMMY_ROLE): ?>
                                        <strong><?= Yii::t('xenon', 'Role') ?>:</strong>  <?= $item->role ?>
                                    <?php else: ?>
                                        <strong><?= Yii::t('xenon', 'User') ?>:</strong>
                                        <?= $item->user_id ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-4">
                                    <?= \eseperio\filescatalog\widgets\CrudStatus::widget(['model' => $item]) ?>
                                </div>
                                <div class="col-sm-4">
                                    <?= Html::a(Yii::t('xenon', 'Delete'), [
                                        'remove-acl',

                                    ], [
                                        'class' => 'pull-right',
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => Yii::t('xenon', 'Confirm deletetion'),
                                            'params'=>[
                                                'inode_id' => $item->inode_id,
                                                'role' => $item->role,
                                                'user_id' => $item->user_id
                                            ]
                                        ],
                                    ]) ?>
                                </div>
                            </
                            >
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
