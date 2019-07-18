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

namespace eseperio\filescatalog\widgets;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\base\Inode;
use yii\base\InvalidArgumentException;
use yii\base\Widget;
use yii\helpers\Html;

class IconDisplay extends Widget
{

    const ICON_STYLE_VIVID = "viv";
    const ICON_STYLE_CLASSIC = "cla";
    const ICON_STYLE_SQUARED_O = "sqo";
    const SIZE_MD = "md";
    const SIZE_LG = "lg";
    const SIZE_XL = "xl";
    const NORMAL = null;

    /**
     * @var string Style of icons accoridng to dmhendricks/file-icon-vectors
     */
    public $iconStyle;
    /**
     * @var string Size to be used on icons. Leave null to use small size
     */
    public $iconSize;
    /**
     * @var bool whether fit the column size to the size of icons.
     */
    /**
     * @var Inode
     */
    public $model;

    public function run()
    {
        if (!$this->model instanceof Inode && !is_subclass_of($this->model, Inode::class))
            throw new InvalidArgumentException("Model must be an instance of Inode");

        if (empty($this->iconStyle))
            $this->iconStyle = self::ICON_STYLE_SQUARED_O;

        if ($this->model->type === InodeTypes::TYPE_DIR) {
            $icon = "folder";
        } else {
            $icon = empty($this->model->extension) ? "blank" : $this->model->extension;
        }

        $classes = [
            "fiv-" . $this->iconStyle,
            "fiv-icon-" . Html::encode($icon)
        ];

        if (!empty($this->iconSize))
            $classes[] = 'fiv-size-' . $this->iconSize;

        return Html::tag('span', '', [
            'class' => $classes
        ]);
    }
}
