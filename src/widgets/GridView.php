<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\columns\CheckboxColumn;
use eseperio\filescatalog\columns\IconColumn;
use eseperio\filescatalog\columns\InodeActionColumn;
use eseperio\filescatalog\columns\InodeNameColumn;
use eseperio\filescatalog\columns\InodeUuidColumn;
use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class GridView
 * @package eseperio\filescatalog\widgets
 */
class GridView extends \yii\grid\GridView
{

    /**
     * @var array
     */
    public $tableOptions = ['class' => 'table table-striped'];

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->id = 'filex-grid';
        $this->registerAssets();
        if (empty($this->columns))
            $this->setColumns();
        parent::init();
    }

    private function registerAssets(): void
    {
        $view = Yii::$app->view;
        FileTypeIconsAsset::register($view);
        $view = \Yii::$app->view;
        $view->registerCss(<<<CSS
 td.ic-cl-fit, 
 th.ic-cl-fit {
    white-space: nowrap;
    width: 1%;
}
CSS
        );

    }

    /**
     *
     */
    public function setColumns()
    {
        $this->columns = [
            ['class' => CheckboxColumn::class],
            ['class' => IconColumn::class, 'iconSize' => IconDisplay::SIZE_MD],
            ['class' => InodeNameColumn::class],
            ['class' => InodeUuidColumn::class],
            ['class' => InodeActionColumn::class]
        ];
    }


    /**
     * Attach page size links to the pagination section.
     * @return string
     */
    public function renderPager()
    {
        $pager = parent::renderPager();

        $links = [];
        foreach ([10, 30, 50] as $item) {
            if (Yii::$app->request->get($this->dataProvider->getPagination()->pageSizeParam) == $item) {
                $links[] = Html::tag('span', $item);

            } else {
                $links[] = Html::a($item, Url::current([$this->dataProvider->getPagination()->pageSizeParam => $item]));

            }
        }

        $label = Html::tag('span', Yii::t('filescatalog', 'Per page:')) . " ";

        $perPageWrapper = Html::tag('div', $label . implode("  ", $links), ['class' => 'pull-right pagination']);

        return $pager . $perPageWrapper;
    }
}
