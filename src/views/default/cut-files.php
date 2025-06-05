<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/* @var $cutInodes \eseperio\filescatalog\models\Inode[] */
/* @var $destination \eseperio\filescatalog\models\Inode|null */

/* @var $filexModule \eseperio\filescatalog\FilesCatalogModule */

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\helpers\Html;

?>

<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">
                    <?php if ($destination): ?>
                        <?= Yii::t('filescatalog', 'Move this items to: {0}', [Html::encode($destination->name)]) ?>
                    <?php else: ?>
                        <?= Yii::t('filescatalog', 'Items that has been cut') ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="panel-body">
                <?php if (empty($cutInodes)): ?>
                    <div class="alert alert-info">
                        <?= Yii::t('filescatalog', 'No items have been cut. Use the "Cut" option in the bulk actions menu to cut files or directories.') ?>
                    </div>
                <?php else: ?>
                    <?php if ($destination && !AclHelper::canWrite($destination)): ?>
                        <div class="alert alert-warning">
                            <?= Yii::t('filescatalog', 'You do not have write permissions for this directory. Cannot paste here.') ?>
                        </div>
                    <?php endif; ?>
                    <ul class="list-group">
                        <?php foreach ($cutInodes as $inode): ?>
                            <li class="list-group-item">
                                <?= IconDisplay::widget(['model' => $inode]) ?>
                                <?= Html::encode($inode->name) ?>
                                <?php if ($inode->type === InodeTypes::TYPE_DIR): ?>
                                    <span class="label label-primary"><?= Yii::t('filescatalog', 'Directory') ?></span>
                                <?php elseif ($inode->type === InodeTypes::TYPE_FILE): ?>
                                    <span class="label label-info"><?= Yii::t('filescatalog', 'File') ?></span>
                                <?php elseif ($inode->type === InodeTypes::TYPE_SYMLINK): ?>
                                    <span class="label label-warning"><?= Yii::t('filescatalog', 'Symlink') ?></span>
                                <?php endif; ?>

                                <?php if (!AclHelper::canWrite($inode)): ?>
                                    <span class="label label-danger"><?= Yii::t('filescatalog', 'No write permission') ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if (!empty($cutInodes)): ?>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-xs-6 text-left">
                            <?php if ($destination): ?>
                                <?= Html::a(Yii::t('filescatalog', 'Cancel cut Operation'), ['cut-files', 'cancel' => 1], [
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => Yii::t('filescatalog', 'Are you sure you want to cancel the cut operation?')
                                    ]
                                ]) ?>

                            <?php endif; ?>
                        </div>
                        <div class="col-xs-6 text-right">
                            <?php if ($destination && AclHelper::canWrite($destination)): ?>
                                <?= Html::a(Yii::t('filescatalog', 'Confirm paste'), ['cut-files', 'destination' => $destination->uuid, 'confirm' => 1], [
                                    'class' => 'btn btn-success',
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => Yii::t('filescatalog', 'Are you sure you want to paste these items to this location?')
                                    ]
                                ]) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
