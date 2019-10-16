<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $parent \eseperio\filescatalog\models\Directory */

/* @var $dataProvider \yii\data\ActiveDataProvider */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>


<div class="directory-form">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel">
                <div class="panel-heading">
                    <p><span class="fiv-icon-folder fiv-sqo"></span>
                        <?= Html::a($parent->name, ['index', 'uuid' => $parent->uuid]) ?>
                    </p>
                </div>
                <div class="panel-body">
                    <?= Yii::t('filescatalog', 'Search the item you want to link') ?>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin([
                        'method' => 'GET',
                        'action' => \yii\helpers\Url::to(['default/new-link'])
                    ]); ?>

                    <div class="row">
                        <div class="col-sm-8">
                            <?= $form->field($model, 'query')->textInput(['maxlength' => 256, 'placeholder' => Yii::t('filescatalog', 'Name or uuid')])->label(false) ?>
                            <?= Html::hiddenInput('uuid', Html::encode(Yii::$app->request->get('uuid'))) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= Html::submitButton(Yii::t('filescatalog', 'Search'), ['class' => 'btn btn-default']) ?>

                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>

                </div>
                <div class="panel-body">
                    <?= \yii\widgets\ListView::widget([
                        'dataProvider' => $dataProvider,
                        'itemView' => 'partials/_new-link-item',
                        'layout' => '{summary}<ul class="list-group">{items}</ul>{pager}',
                        'itemOptions' => ['tag' => 'li', 'class' => 'list-group-item clearfix'],
                        'viewParams' => [
                            'parent' => $parent
                        ]
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
