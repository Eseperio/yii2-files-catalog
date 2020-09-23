<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \eseperio\filescatalog\models\Inode */
/* @var $usePjax boolean */
/* @var $bulkActions array */
/* @var $searchModel \eseperio\filescatalog\models\InodeSearch */
/* @var $isDeepSearch boolean */
/* @var $deepSearchParamName string */

/* @var $parents array with the parents inodes */

use eseperio\filescatalog\assets\IndexAsset;
use eseperio\filescatalog\widgets\Breadcrumb;
use eseperio\filescatalog\widgets\GridView;
use yii\bootstrap\Dropdown;
use yii\helpers\Html;
use yii\widgets\Pjax;

IndexAsset::register($this);
$this->registerJs(<<<JS

if (!filexIndexInstance) {
    var filexIndexInstance = new filexIndex();
}

JS
);
$this->title = Yii::t('filescatalog', 'Files catalog');
$pjaxId = 'filex-pjax-idx';
?>
<?php
if ($usePjax)
    Pjax::begin([
        'id' => $pjaxId
    ]);
?>
<div class="panel panel-body">
    <?php if ($isDeepSearch): ?>
        <div class="alert alert-info">
            <div class="row">
                <div class="col-sm-8">
                    <p><strong><?= Yii::t('filescatalog', 'Deep search') ?></strong><br>
                        <?= Yii::t('filescatalog', 'Searching all files in this folder and its subdirectories') ?>
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <?= Html::a(Yii::t('filescatalog', 'Close deep search'), ['/filex/default/view', 'uuid' => $model->uuid], [
                        'class' => 'btn btn-default '
                    ]) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?= Breadcrumb::widget([
        'model' => $model,
        'pjaxId' => $pjaxId
    ]) ?>

    <hr>
    <?php
    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
    ]);
    ?>
</div>
<div>
    <?php if ($isDeepSearch): ?>
        <?= Html::a(Yii::t('filescatalog', 'Close deep search'), ['/filex/default/view', 'uuid' => $model->uuid], [
            'class' => 'btn btn-default '
        ]) ?>

    <?php else: ?>
        <?= Html::a(Yii::t('filescatalog', 'Deep search this directory'), \yii\helpers\Url::current(['deep' => 1]), [
            'data' => [
                'pjax' => 0
            ]
        ]) ?>
    <?php endif; ?>
</div>
<?php
if ($usePjax)
    Pjax::end();
?>
<div class="filex-bulk-actions collapse" id="filex-bulk-actions">
    <div class="dropdown">
        <a href="#" data-toggle="dropdown"
           class="dropdown-toggle btn btn-default"><?= Yii::t('filescatalog', 'Bulk actions') ?> <b
                    class="caret"></b></a>
        <?php
        echo Dropdown::widget([
            'items' => $bulkActions,
        ]);
        ?>
    </div>
</div>
