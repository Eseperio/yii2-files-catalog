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
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Inode;
use Yii;
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

        if (!AclHelper::can($model, AccessControl::ACTION_WRITE)) {
            $nameTag .= Html::tag('span', Yii::t('filescatalog', 'Read only'), ['class' => 'text-muted']);
        }
        $displayExtension = ($model->type === InodeTypes::TYPE_FILE && !empty($model->extension));
        $realName = Html::encode($model->name . ($displayExtension ? "." . $model->extension : ""));
        $realNameTag = Html::tag('div', $realName, ['class' => 'text-muted small']);

        $separator = "<br>";

        return $nameTag . $separator . $realNameTag;
    }

}
