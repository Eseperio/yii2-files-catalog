<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\helpers;


use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class Helper
{
    public static function humanize($string)
    {
        return Html::encode(StringHelper::mb_ucfirst(Inflector::camel2words($string, false)));

    }
}
