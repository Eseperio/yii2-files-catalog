<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\Inode|\eseperio\filescatalog\models\File */

/* @var $parent \eseperio\filescatalog\models\Inode */
/* @var $parentTreeNodes \eseperio\filescatalog\models\Inode[] */
/* @var $maxTreeDepth int */

/* @var $childrenTreeNodes \eseperio\filescatalog\models\Inode[] */

/* @var $accessControlFormModel InodePermissionsForm */

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\InodePermissionsForm;
use eseperio\filescatalog\widgets\CrudStatus;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
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
        echo $form->field($accessControlFormModel, 'inode_id')->hiddenInput()->label(false);

        ?>
        <div class="">
            <?php
            echo $form->field($accessControlFormModel, 'type')->radioList([
                InodePermissionsForm::TYPE_USER => Yii::t('filescatalog', 'User'),
                InodePermissionsForm::TYPE_ROLE => Yii::t('filescatalog', 'Role'),
            ]);
            ?>
        </div>
        <div class="row">

            <div class="col-sm-12">
                <?= $form->field($accessControlFormModel, 'user_id', [
                    'options' => ['class' => 'form-group ' . ($accessControlFormModel->type !== InodePermissionsForm::TYPE_USER ? "collapse" : "")]
                ])->textInput(['type' => 'number']);
                ?>
            </div>
            <div class="col-sm-12 filex-role-input <?= $accessControlFormModel->type !== InodePermissionsForm::TYPE_ROLE ? "collapse" : "" ?>">
                <h4><?= Yii::t('filescatalog', 'Wildcards') ?></h4>
                <p><?= Yii::t('filescatalog', '{wildcard} allow everyone access to this item.', [
                        'wildcard' => Html::tag('strong', AccessControl::WILDCARD_ROLE, ['class' => 'text-info'])
                    ]) ?></p>
                <p><?= Yii::t('filescatalog', '{wildcard} allow everyone logged in access to this item.', [
                        'wildcard' => Html::tag('strong', AccessControl::LOGGED_IN_USERS, ['class' => 'text-info'])
                    ]) ?></p>
                <?= $form->field($accessControlFormModel, 'role')->textInput();
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?= $form->field($accessControlFormModel, 'crud')->checkboxList([
                    AccessControl::ACTION_READ => Yii::t('filescatalog', 'Read'),
                    AccessControl::ACTION_WRITE => Yii::t('filescatalog', 'Update'),
                    AccessControl::ACTION_DELETE => Yii::t('filescatalog', 'Delete')
                ]); ?>
            </div>
        </div>
        <?php
        echo Html::submitButton(Yii::t('filescatalog', 'Add permission'), ['class' => 'btn btn-primary']);
        ActiveForm::end();
        ?>
    </div>
    <div class="panel-heading">
        <div class="panel-title"><?= Yii::t('filescatalog', 'Current permissions') ?></div>
    </div>
    <div class="panel-body">
        <?php if ($model->type === InodeTypes::TYPE_VERSION): ?>
            <div class="text-warning">
                <?= Yii::t('filescatalog', 'Permissions are for the original and all versions.') ?>
            </div>
        <?php endif; ?>
        <ul class="list-group"><?php


            $accessControls = ($model->type == InodeTypes::TYPE_VERSION) ? $model->original->accessControlList:$model->accessControlList;
            foreach ($accessControls as $item) :?>
                <li class="list-group-item">
                    <div class="row">

                        <div class="col-sm-4">
                            <?php
                            switch ($item->role) {
                                case AccessControl::WILDCARD_ROLE:
                                    echo Html::tag('div', Yii::t('filescatalog', 'Everyone'), ['class' => 'label label-danger']);
                                    break;
                                case AccessControl::LOGGED_IN_USERS:
                                    echo Html::tag('div', Yii::t('filescatalog', 'All logged in'), ['class' => 'label label-warning']);
                                    break;
                                case AccessControl::DUMMY_ROLE:
                                    echo Html::tag('strong', Yii::t('filescatalog', 'User'))
                                        . ": " . $item->user_id;
                                    break;
                                default:
                                    echo Html::tag('strong', Yii::t('filescatalog', 'Role'))
                                        . ": " . Html::encode($item->role);
                                    break;
                            }
                            ?>

                        </div>
                        <div class="col-sm-4">
                            <?= CrudStatus::widget(['model' => $item]) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= Html::a(Yii::t('filescatalog', 'Delete'), [
                                'remove-acl',
                            ], [
                                'class' => 'pull-right',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => Yii::t('filescatalog', 'Confirm deletion'),
                                    'params' => [
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

