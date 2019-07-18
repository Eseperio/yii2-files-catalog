<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \eseperio\filescatalog\models\Inode */
/* @var $usePjax boolean */

/* @var $parents array with the parents inodes */

use eseperio\filescatalog\widgets\GridView;
use eseperio\filescatalog\widgets\Uploader;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\Pjax;


$this->title = Yii::t('filescatalog', 'Files catalog');
$pjaxId = 'filex-pjax-idx';
?>
<?= Html::a('Fake', ['fake']) ?>
<?php
if ($usePjax)
    Pjax::begin([
        'id' => $pjaxId
    ]);
?>
<div class="row">
    <div class="col-sm-8">
        <h2>
            <span class="fiv-sqo fiv-icon-folder"></span>
            <?= $model->name ?></h2>
        <p class="text-muted"><?= join('/', ArrayHelper::map($parents, 'uuid', function ($item) {
                return Html::a(Inflector::camel2words($item['name']), ['index', 'uuid' => $item['uuid']]);
            })) ?></p>
    </div>
    <div class="col-sm-4 text-right">
        <div class="h2">
            <?= Html::a(Yii::t('filescatalog', 'Add folder'), ['new-folder','uuid'=>$model->uuid],['class'=>'btn btn-primary']) ?>

            <?= Uploader::widget(['targetUuid' => $model->uuid,
                'pjaxId' => $pjaxId]) ?>

        </div>
    </div>
</div>
<hr>
<?php
echo GridView::widget(['dataProvider' => $dataProvider]);
if ($usePjax)
    Pjax::end();
?>
