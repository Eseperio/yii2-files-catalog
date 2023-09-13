<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use yii\helpers\Html;
use eseperio\filescatalog\models\InodeQuery;
use eseperio\filescatalog\widgets\IconDisplay;
use yii\grid\DataColumn;

class IconColumn extends DataColumn
{

    /**
     * @inheritdoc
     */
    public $attribute = 'extension';
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

        if(empty($this->label))
            $this->label="";
        $this->initFilterValues();
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
     * Initializes filter option values by grouping results.
     */
    public function initFilterValues()
    {
//        @todo: Disabled until a better way of handling searches is found
        return;
        if (empty($this->filter) && $this->grid->dataProvider instanceof \yii\data\ActiveDataProvider) {
            $query = $this->grid->dataProvider->query;

            /** @var InodeQuery $filterQuery */
            $filterQuery = clone($query);
            $filterQuery->join = null;
            $extensions = $filterQuery->select(['extension', 'id'])
                ->groupBy('extension')
                ->column();

            $this->filter = array_combine($extensions, $extensions);
        }
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
