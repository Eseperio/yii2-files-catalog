<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $models \eseperio\filescatalog\models\Inode[] */
/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */

/* @var $hash string */
/* @var $formModel \yii\base\DynamicModel */
/* @var $accessControlFormModel InodePermissionsForm */

/* @var $error string */

use eseperio\filescatalog\models\InodePermissionsForm;
use yii\bootstrap\Html;

?>
<h1><?= Yii::t('filescatalog', 'Bulk access control') ?></h1>
<?php $form = \yii\bootstrap\ActiveForm::begin([]) ?>

<div class="row">
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">
                    <?= Yii::t('filescatalog', 'Items to be affected') ?>
                </div>
            </div>
            <div class="panel-body">

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <?= Html::ul($models, [
                    'item' => function ($model) use ($formModel) {
                        /* @var $model \eseperio\filescatalog\models\Inode */
                        $link = Html::a(Html::encode($model->name), ['/filex/default/view', 'uuid' => $model->uuid], ['target' => '_blank']);
                        $input = Html::hiddenInput(Html::getInputName($formModel, 'uuids[]'), $model->uuid);
                        $label = Html::tag('li', $link . $input, ['class' => 'list-group-item']);

                        return $label;
                    },
                    'class' => 'list-group'
                ]) ?>
            </div>

        </div>
    </div>
    <div class="col-sm-6">
        <div class="panel panel-default">
            <div class="panel-body">

                <?php /** @var \eseperio\filescatalog\FilesCatalogModule $filexModule */
                ?>
                <div class="col-md-6">
                    <?= $this->render('partials/_acl', [
                        'accessControlFormModel' => $accessControlFormModel,
                        'model' => $model,
                        'filexModule' => $filexModule
                    ]) ?>
                </div>
            </div>

            <div class="panel-body">
                <?= Html::hiddenInput($filexModule->secureHashParamName, $hash) ?>
                <?= Html::a(Yii::t('filescatalog', 'Cancel'), \yii\helpers\Url::previous(), ['class' => 'btn btn-primary']) ?>
                <?= Html::submitButton(Yii::t('filescatalog', 'Add permissions'), ['class' => 'btn btn-danger pull-right']) ?>
            </div>
        </div>
    </div>
</div>
<?php
\yii\bootstrap\ActiveForm::end()
?>

