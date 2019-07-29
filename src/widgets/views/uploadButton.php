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
$label = $isVersion ? Yii::t('filescatalog', 'Add version') : Yii::t('filescatalog', 'Add files')
?>


<span class="btn btn-default fileinput-button" data-toggle="tooltip" title="<?= $label ?>">
    <i class="<?= $addFilesIconClass ?>"></i>
   <span>
       <?php /** @var boolean $showLabels */
       if ($showLabels || $isVersion): ?>
           <?= $label ?>
       <?php endif; ?>
   </span>
    <!-- The file input field used as target for the file upload widget -->
    <?= $input ?>
</span>

