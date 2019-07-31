<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\helpers\AclHelper;

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
//        @todo: Hide those checkbox when user can not delete.
        if ($this->fitToContent)
            $this->registerAssets();

        $this->checkboxOptions = function ($model) {
            if (!AclHelper::canDelete($model))
                return ['disabled' => 1, 'class' => 'collapse'];
        };
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
