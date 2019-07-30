<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\models\base\Inode;

class CheckboxColumn extends \yii\grid\CheckboxColumn
{


    public $name = 'filex-bulk-action';
    /**
     * @var bool whether fit the column size to the size of icons.
     */
    public $fitToContent = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
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

}
