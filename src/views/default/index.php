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

/* @var $parents array with the parents inodes */

use eseperio\filescatalog\widgets\GridView;
use yii\helpers\Html;
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
echo \eseperio\filescatalog\widgets\Breadcrumb::widget([
    'model' => $model,
    'pjaxId' => $pjaxId
]) ?>
<hr>
<?php
echo GridView::widget(['dataProvider' => $dataProvider]);
if ($usePjax)
    Pjax::end();
?>
