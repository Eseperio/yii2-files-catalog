<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\grid\DataColumn;

class IconColumn extends DataColumn
{


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
    public $fitToContent = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty($this->iconStyle))
            $this->iconStyle = IconDisplay::ICON_STYLE_SQUARED_O;

        if ($this->fitToContent)
            $this->registerAssets();

        parent::init();
    }

    /**
     * @param $view
     */
    private function registerAssets(): void
    {

        Html::addCssClass($this->headerOptions, 'ic-cl-fit');
        Html::addCssClass($this->filterOptions, 'ic-cl-fit');
        Html::addCssClass($this->contentOptions, 'ic-cl-fit');
        Html::addCssClass($this->footerOptions, 'ic-cl-fit');
    }

    /**
     * @param $model
     * @param $key
     * @param $index
     * @return string
     * @throws \Exception
     */
    public function renderDataCellContent($model, $key, $index)
    {
        return IconDisplay::widget([
            'model' => $model,
            'iconSize' => $this->iconSize,
            'iconStyle' => $this->iconStyle
        ]);
    }
}
