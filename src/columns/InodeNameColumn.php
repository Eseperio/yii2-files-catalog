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
use eseperio\filescatalog\traits\ModuleAwareTrait;
use yii\grid\DataColumn;

/**
 * Class InodeNameColumn
 * @package eseperio\filescatalog\columns
 */
class InodeNameColumn extends DataColumn
{
    use ModuleAwareTrait;
    /**
     * @var null|string Html or text indicating read only permissions
     */
    public $readOnlyMessage = null;
    /**
     * @var string the attribute used to display.
     */
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
            if (!empty($this->module->readOnlyMessage))
                $this->readOnlyMessage = $this->module->readOnlyMessage;

            $nameTag .= $this->readOnlyMessage;
        }
        $displayExtension = ($model->type === InodeTypes::TYPE_FILE && !empty($model->extension));
        $realName = Html::encode($model->name . ($displayExtension ? "." . $model->extension : ""));
        $realNameTag = Html::tag('div', $realName, ['class' => 'text-muted small']);

        $separator = "<br>";

        return $nameTag . $separator . $realNameTag;
    }


}
