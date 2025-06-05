<?php
/**
 * @var \yii\web\View $this
 * @var \eseperio\filescatalog\widgets\DirectoryTreeWidget $widget
 * @var string $id
 */

use yii\helpers\Html;
?>

<div id="<?= $id ?>-container" class="directory-tree-container">
    <ul class="directory-tree">
        <li class="loading"><?= Yii::t('filescatalog','Loading...') ?></li>
    </ul>
</div>
