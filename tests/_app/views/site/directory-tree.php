<?php
/**
 * Test view for DirectoryTreeWidget
 * 
 * @var \yii\web\View $this
 * @var \yii\base\DynamicModel $model
 */

use eseperio\filescatalog\widgets\DirectoryTreeWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Directory Tree Widget Test';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-directory-tree">
    <h1><?= Html::encode($this->title) ?></h1>


    <div class="row">
        <div class="col-md-6">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'selection')->widget(DirectoryTreeWidget::class, [
                'rootNodeUuid' => '4a8dd54e-1204-4308-bd6c-c935afe0e580',
                'mode' => DirectoryTreeWidget::MODE_DIRECTORIES_ONLY,
//                'extensions' => ['jpg', 'png', 'gif', 'pdf', 'txt'],
                'multiple' => true,
            ]) ?>

            <div class="form-group">
                <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h3>Widget Usage Instructions</h3>
            <p>This widget allows you to select directories and/or files from a directory tree.</p>
            <ul>
                <li>Click on the [+] icon to expand a directory</li>
                <li>Click on a directory or file name to select it</li>
                <li>Multiple selection is enabled</li>
            </ul>

            <h3>Widget Configuration Options</h3>
            <ul>
                <li><strong>rootDirectory</strong>: The root directory path (default: @webroot)</li>
                <li><strong>mode</strong>: The mode of elements to show (MODE_DIRECTORIES_ONLY or MODE_ALL)</li>
                <li><strong>extensions</strong>: File extensions to show (e.g. ['jpg', 'png', 'pdf'])</li>
                <li><strong>multiple</strong>: Whether to allow multiple selection (default: false)</li>
            </ul>
        </div>
    </div>
</div>
