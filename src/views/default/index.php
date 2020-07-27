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

/* @var $parents array with the parents inodes */

use eseperio\filescatalog\assets\IndexAsset;
use eseperio\filescatalog\widgets\Breadcrumb;
use eseperio\filescatalog\widgets\GridView;
use yii\bootstrap\Dropdown;
use yii\widgets\Pjax;

IndexAsset::register($this);

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
