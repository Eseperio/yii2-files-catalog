<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;

use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\base\Inode;
use yii\grid\DataColumn;

/**
 * Class InodeNameColumn
 * @package eseperio\filescatalog\columns
 */
class InodeNameColumn extends DataColumn
{
    public $attribute = "name";

    /**
     * @param $model Inode
     * @param $key
     * @param $index
     * @return string
     */
    public function renderDataCellContent($model, $key, $index)
    {
        $humanized = $model->humanName;
        $nameTag = Html::tag('b', $humanized, []);
        $displayExtension = ($model->type === InodeTypes::TYPE_FILE && !empty($model->extension));
        $realName = Html::encode($model->name . ($displayExtension ? "." . $model->extension : ""));
        $realNameTag = Html::tag('div', $realName, ['class' => 'text-muted']);

        $separator = "<br>";

        return $nameTag . $separator . $realNameTag;
    }

}
