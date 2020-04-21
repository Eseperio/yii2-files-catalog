<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
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
