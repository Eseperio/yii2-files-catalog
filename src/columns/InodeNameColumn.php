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
use yii\grid\DataColumn;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * Class InodeNameColumn
 * @package eseperio\filescatalog\columns
 */
class InodeNameColumn extends DataColumn
{
    /**
     * @param $model
     * @param $key
     * @param $index
     * @return string
     */
    public function renderDataCellContent($model, $key, $index)
    {
        $humanized = Html::encode(StringHelper::mb_ucfirst(Inflector::camel2words($model->name, false)));
        $nameTag = Html::tag('b', $humanized, []);
        $displayExtension= ($model->type === InodeTypes::TYPE_FILE && !empty($model->extension));
        $realName = Html::encode($model->name . ($displayExtension ? "." . $model->extension : ""));
        $realNameTag = Html::tag('div', $realName, ['class' => 'text-muted']);

        $separator = "<br>";

        return $nameTag . $separator . $realNameTag;
    }

}
