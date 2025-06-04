<?php

namespace eseperio\filescatalog\assets;

use yii\web\AssetBundle;

/**
 * Asset bundle for the DirectoryTreeWidget
 */
class DirectoryTreeWidgetAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/directory-tree.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/directory-tree.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset',
        FileTypeIconsAsset::class,
    ];
}
