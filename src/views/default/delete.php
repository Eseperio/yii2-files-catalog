<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $model \eseperio\filescatalog\models\Inode */

/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */

use yii\helpers\Html; ?>

<div class="row">
    <div class="col-sm-4 col-sm-offset-4">
        <?= Html::beginForm() ?>
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h1 class="panel-title"><?= Yii::t('filescatalog', 'Delete directory') ?></h1>
            </div>
            <div class="panel-body">

                <p class="text-danger"><?= Yii::t('filescatalog', 'This operation can not be undone') ?></p>
                <p><?= Yii::t('filescatalog', 'To delete this folder and all its contents, write the first {nchar} characters of directory name', [
                        'nchar' => mb_strlen($model->getDeletionConfirmText())
                    ]) ?></p>
                <p>
                    <strong class="text-primary"><?= $model->getDeletionConfirmText() ?></strong><?= Html::encode(mb_substr($model->name, mb_strlen($model->getDeletionConfirmText()))) ?>
                </p>
                <?php
                echo Html::beginForm(['/']);
                echo Html::textInput('confirm_text', '', ['class' => 'form-control']);
                echo Html::hiddenInput($filexModule->secureHashParamName, $model->deleteHash)
                ?>
            </div>
            <div class="panel-footer clearfix">
                <?= Html::a(Yii::t('filescatalog', 'Cancel'), \yii\helpers\Url::previous(), ['class' => 'btn btn-primary']) ?>
                <?= Html::submitButton(Yii::t('filescatalog', 'Delete permanently'), ['class' => 'btn btn-danger pull-right']) ?>
            </div>
        </div>
        <?= Html::endForm(); ?>
    </div>
</div>
