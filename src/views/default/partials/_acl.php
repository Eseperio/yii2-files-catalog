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
            <div class="col-sm-12 filex-role-input">
                <p><?= Yii::t('xenon', 'You can use also the wildcard {wildcard} to give access to everyone.', [
                        'wildcard' => Html::tag('strong', AccessControl::WILDCARD_ROLE,['class'=>'label label-info'])
                    ]) ?></p>
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
                            <?php if ($item->role === AccessControl::WILDCARD_ROLE): ?>
                                <div class="label label-warning"><?= Yii::t('xenon', 'EVERYONE') ?></div>
                            <?php elseif ($item->role !== AccessControl::DUMMY_ROLE): ?>
                                <strong><?= Yii::t('xenon', 'Role') ?>
                                    :</strong>  <?= Html::encode($item->role) ?>
                            <?php else: ?>
                                <strong><?= Yii::t('xenon', 'User') ?>:</strong>
                                <?= $item->user_id ?>
                            <?php endif; ?>
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

