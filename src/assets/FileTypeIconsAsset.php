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

namespace eseperio\filescatalog\assets;


use yii\web\AssetBundle;

class FileTypeIconsAsset extends AssetBundle
{

    public $sourcePath = "@vendor/dmhendricks/file-icon-vectors/dist";

    public $css = [
        'file-icon-square-o.css'
    ];
}
