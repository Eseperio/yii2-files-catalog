<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\assets;


use yii\web\AssetBundle;

class IndexAsset extends AssetBundle
{

    public $sourcePath = __DIR__ . DIRECTORY_SEPARATOR . "dist";

    public $js = [
        'js/filex-index' . (YII_ENV_PROD ? ".min" : "") . ".js"
    ];

    public $css=[
      'css/index.css'
    ];

}
