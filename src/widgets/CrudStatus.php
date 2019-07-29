<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;

use yii\helpers\ArrayHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\base\Inode;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Widget;

class CrudStatus extends Widget
{

    /**
     * @var AccessControl
     */
    public $model;


    public function init()
    {
        if (empty($this->model))
            throw new InvalidArgumentException(__CLASS__ . '::model is required');

        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        $mask = $this->model->getCrud();

        $readLabel = Yii::t('filescatalog', 'Read');
        $html = Html::tag('span', substr($readLabel, 0, 1), [
            'class' => 'label label-' . (in_array(AccessControl::ACTION_READ, $mask) ? "danger" : "default"),
            'title' => $readLabel, 'data-toggle' => 'tooltip'
        ]);
        $updateLabel = Yii::t('filescatalog', 'Write');
        $html .= Html::tag('span', substr($updateLabel, 0, 1), [
            'class' => 'label label-' . (in_array(AccessControl::ACTION_WRITE, $mask) ? "danger" : "default"),
            'title' => $updateLabel, 'data-toggle' => 'tooltip'
        ]);
        $deleteLabel = Yii::t('filescatalog', 'Delete');
        $html .= Html::tag('span', substr($deleteLabel, 0, 1), [
            'class' => 'label label-' . (in_array(AccessControl::ACTION_DELETE, $mask) ? "danger" : "default"),
            'title' => $deleteLabel, 'data-toggle' => 'tooltip'
        ]);

        return $html;
    }
}
