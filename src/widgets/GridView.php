<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\columns\IconColumn;
use eseperio\filescatalog\columns\InodeActionColumn;
use eseperio\filescatalog\columns\InodeNameColumn;
use eseperio\filescatalog\columns\InodeUuidColumn;
use Yii;

/**
 * Class GridView
 * @package eseperio\filescatalog\widgets
 */
class GridView extends \yii\grid\GridView
{

    public $showHeader = false;
    /**
     * @var array
     */
    public $tableOptions = ['class' => 'table table-striped'];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->registerAssets();
        $this->setColumns();
        parent::init();
    }

    private function registerAssets(): void
    {
        $view = Yii::$app->view;
        FileTypeIconsAsset::register($view);

    }

    /**
     *
     */
    public function setColumns()
    {
        $this->columns = [
            ['class' => IconColumn::class, 'iconSize' => IconDisplay::SIZE_MD],
            ['class' => InodeNameColumn::class],
            ['class' => InodeUuidColumn::class],
            ['class' => InodeActionColumn::class]
        ];
    }
}
