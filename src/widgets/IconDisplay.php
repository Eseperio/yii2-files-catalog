<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
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
        if (!$this->model instanceof Inode && !is_subclass_of($this->model, Inode::class)
            && (is_array($this->model) && !array_key_exists('extension', $this->model)))
            throw new InvalidArgumentException("Model must be an instance of Inode or have property extension");

        if (empty($this->iconStyle))
            $this->iconStyle = self::ICON_STYLE_SQUARED_O;

        $type = $this->model['type'];

        $isSymlink = $type == InodeTypes::TYPE_SYMLINK;
        if ($isSymlink)
            $type = (int)$this->model['symlink_type'];
        if ($type === InodeTypes::TYPE_DIR) {

            $icon = "folder";
        } else {
            $extension = ($isSymlink ? $this->model['symlink_extension'] : $this->model['extension']);
            $icon = empty($extension) ? "blank" : $extension;
        }

        $classes = [
            "fiv-" . $this->iconStyle,
            "fiv-icon-" . Html::encode($icon),
            "fc-icon"
        ];

        if ($this->model->type == InodeTypes::TYPE_SYMLINK)
            $classes[] = "symlink";
        if (!empty($this->iconSize))
            $classes[] = 'fiv-size-' . $this->iconSize;

        return Html::tag('span', '', [
            'class' => $classes
        ]);
    }
}
