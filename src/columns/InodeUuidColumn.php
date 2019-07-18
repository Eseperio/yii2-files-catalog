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

namespace eseperio\filescatalog\columns;


use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\grid\DataColumn;

/**
 * Class InodeUuidColumn
 * @package eseperio\filescatalog\columns
 */
class InodeUuidColumn extends DataColumn
{
    use ModuleAwareTrait;

    /**
     *
     */
    public function init()
    {
        $this->registerAssets();
        parent::init();
    }

    /**
     *
     */
    public function registerAssets()
    {
        \Yii::$app->view->registerCss(<<<CSS
.fc-uuid-cl {
display: none;
}
tr:hover .fc-uuid-cl {
display: block;
}
CSS
        );
    }

    /**
     * @param $model
     * @param $key
     * @param $index
     * @return string
     */
    public function renderDataCellContent($model, $key, $index)
    {
        $html = Html::tag('div', $model->uuid, ['class' => 'fc-uuid-cl text-center text-muted']);
        if ($this->module->checkFilesIntegrity && $model->type == InodeTypes::TYPE_FILE)
            $html .= Html::tag('div', Yii::t('filescatalog', 'md5 Checksum') . ": " . $model->md5hash, ['class' => 'fc-uuid-cl text-center text-muted']);

        return $html;
    }

}
