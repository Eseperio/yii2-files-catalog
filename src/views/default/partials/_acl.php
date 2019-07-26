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
                <h4><?= Yii::t('xenon', 'Wildcards') ?></h4>
                <p><?= Yii::t('xenon', '{wildcard} allow everyone access to this item.', [
                        'wildcard' => Html::tag('strong', AccessControl::WILDCARD_ROLE, ['class' => 'text-info'])
                    ]) ?></p>
                <p><?= Yii::t('xenon', '{wildcard} allow everyone logged in access to this item.', [
                        'wildcard' => Html::tag('strong', AccessControl::LOGGED_IN_USERS, ['class' => 'text-info'])
                    ]) ?></p>
                <?= $form->field($accessControlFormModel, 'role')->textInput();
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <?= $form->field($accessControlFormModel, 'crud')->checkboxList([
                    AccessControl::ACTION_CREATE => Yii::t('filescatalog', 'Create'),
                    AccessControl::ACTION_READ => Yii::t('filescatalog', 'Read'),
                    AccessControl::ACTION_UPDATE => Yii::t('filescatalog', 'Update'),
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
        <ul class="list-group"><?php
            foreach ($model->accessControlList as $item) :?>
                <li class="list-group-item">
                    <div class="row">

                        <div class="col-sm-4">
                            <?php
                            switch ($item->role) {
                                case AccessControl::WILDCARD_ROLE:
                                    echo Html::tag('div', Yii::t('xenon', 'Everyone'), ['class' => 'label label-danger']);
                                    break;
                                case AccessControl::LOGGED_IN_USERS:
                                    echo Html::tag('div', Yii::t('xenon', 'All logged in'), ['class' => 'label label-warning']);
                                    break;
                                case AccessControl::DUMMY_ROLE:
                                    echo Html::tag('strong', Yii::t('xenon', 'User'))
                                        . ": " . $item->user_id;
                                    break;
                                default:
                                    echo Html::tag('strong', Yii::t('xenon', 'Role'))
                                        . ": " . Html::encode($item->role);
                                    break;
                            }
                            ?>

                        </div>
                        <div class="col-sm-4">
                            <?= CrudStatus::widget(['model' => $item]) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= Html::a(Yii::t('xenon', 'Delete'), [
                                'remove-acl',
                            ], [
                                'class' => 'pull-right',
                                'data' => [
                                    'method' => 'post',
                                    'confirm' => Yii::t('xenon', 'Confirm deletetion'),
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

