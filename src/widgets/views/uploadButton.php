<?php
/**
 * Copyright (c) 2019. Grupo Smart (Spain)
 *
 * This software is protected under Spanish law. Any distribution of this software
 * will be prosecuted.
 *
 * Developed by Waizabú <code@waizabu.com>
 * Updated by: erosdelalamo on 18/7/2019
 *
 *
 */

/** @var \dosamigos\fileupload\FileUpload $this */
/** @var string $input the code for the input */
?>

<div class="filex-upload-btn inline"><span class="btn btn-default fileinput-button">
   <i class="glyphicon glyphicon-cloud-upload"></i>
   <span><?= Yii::t('filescatalog', 'Add files') ?></span>
        <!-- The file input field used as target for the file upload widget -->
    <?= $input ?>
</span></div>
<div class="progress collapse" id="<?= $id . "-progress" ?>">
    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
         style="width: 0%;">
    </div>
</div>

<div id="<?= $id ?>-errors">

</div>
