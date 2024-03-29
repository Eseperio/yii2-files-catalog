<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use yii\helpers\Html;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;

class CheckboxColumn extends \yii\grid\CheckboxColumn
{


    public $name = 'filex-bulk-action';
    /**
     * @var bool whether fit the column size to the size of icons.
     */
    public $fitToContent = true;

    private $paramsNameCacheId = 'filex-name-cache-chkcolumn';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (empty(\Yii::$app->params[$this->paramsNameCacheId]))
            \Yii::$app->params[$this->paramsNameCacheId] = 0;

        $this->name = $this->name . "_" . \Yii::$app->params[$this->paramsNameCacheId]++;
        if ($this->fitToContent)
            $this->registerAssets();

        $this->checkboxOptions = function ($model) {
            $options = [
                'value' => $model->uuid
            ];
            if ($model->type == InodeTypes::TYPE_SYMLINK)
                $options['value'] .= "|" . $model->created_at;

            if (!AclHelper::canDelete($model)) {
                $options['disabled'] = 1;
                $options['class'] = 'collapse';
            }

            return $options;
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
