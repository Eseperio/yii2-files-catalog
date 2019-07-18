<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/** @var \dosamigos\fileupload\FileUpload $this */
/** @var string $input the code for the input */
?>

<span class="btn btn-default fileinput-button">
   <i class="glyphicon glyphicon-cloud-upload"></i>
   <span><?= Yii::t('filescatalog', 'Add files') ?></span>
    <!-- The file input field used as target for the file upload widget -->
    <?= $input ?>
</span>

