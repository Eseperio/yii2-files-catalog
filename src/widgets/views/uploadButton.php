<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/** @var \dosamigos\fileupload\FileUpload $this */
/** @var string $input the code for the input */
/* @var boolean $isVersion whether uploader is for a version */
/** @var string $addFilesIconClass */
?>

<span class="btn btn-default fileinput-button">
    <i class="<?= $addFilesIconClass ?>"></i>
   <span>
       <?php /** @var boolean $showLabels */
       if ($showLabels): ?>
           <?= $isVersion ? Yii::t('filescatalog', 'Add version') : Yii::t('filescatalog', 'Add files') ?>
       <?php endif; ?>
   </span>
    <!-- The file input field used as target for the file upload widget -->
    <?= $input ?>
</span>

