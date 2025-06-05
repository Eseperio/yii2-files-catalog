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

/* @var $error string */

use eseperio\filescatalog\dictionaries\InodeTypes;
use yii\helpers\Html;

?>
<h1><?= Yii::t('filescatalog', 'Cut Items') ?></h1>
<?= Html::beginForm() ?>

<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">
                    <?= Yii::t('filescatalog', 'Items to be cut') ?>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <?= Html::ul($models, [
                    'item' => function ($model) {
                        /* @var $model \eseperio\filescatalog\models\Inode */
                        $link = Html::a(Html::encode($model->name), ['/filex/default/view', 'uuid' => $model->uuid], ['target' => '_blank']);
                        $input = Html::hiddenInput('uuids[]', $model->uuid . ($model->type == InodeTypes::TYPE_SYMLINK ? "|" . $model->created_at : ""));
                        $content = $link . $input;
                        if($model->type==InodeTypes::TYPE_SYMLINK)
                            $content.=" ".Html::tag('span',Yii::t('filescatalog','Symlink'),['class'=>'label label-warning']);
                        $label = Html::tag('li', $content, ['class' => 'list-group-item']);

                        return $label;
                    },
                    'class' => 'list-group'
                ]) ?>
                <p><?= Yii::t('filescatalog', 'To confirm cut operation, write the next string in the text input and click confirm') ?></p>
                <p class="h4 text-info"><?= mb_substr($hash, 0, 5) ?></p>
                <?php
                echo Html::textInput('confirm_text', '', ['class' => 'form-control']);
                echo Html::hiddenInput($filexModule->secureHashParamName, $hash)
                ?>
            </div>
            <div class="panel-footer clearfix">
                <?= Html::a(Yii::t('filescatalog', 'Cancel'), \yii\helpers\Url::previous(), ['class' => 'btn btn-primary']) ?>
                <?= Html::submitButton(Yii::t('filescatalog', 'Cut items'), ['class' => 'btn btn-warning pull-right']) ?>
            </div>
        </div>
    </div>
</div>
<?php
Html::endForm();
?>
