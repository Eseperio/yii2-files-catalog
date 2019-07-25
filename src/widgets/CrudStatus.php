<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;

use eseperio\filescatalog\models\base\Inode;
use yii\base\InvalidArgumentException;
use yii\base\Widget;
use Yii;
use app\helpers\Html;
use app\modules\status\models\Status;

class CrudStatus extends Widget
{

    /**
     * @var Inode
     */
    public $model;


    public function init()
    {
        if (empty($this->model))
            throw new InvalidArgumentException(__CLASS__ . '::model is required');

        parent::init();
    }

    public function run()
    {
        $mask = $this->model->getCrud();
        $createLabel = Yii::t('gvp', 'Create');
        $html = Html::tag('span', substr($createLabel, 0, 1), [
            'class' => 'label label-' . (in_array(Status::ACTION_CREATE, $mask) ? "danger" : "default"),
            'title' => $createLabel, 'data-toggle' => 'tooltip'
        ]);

        $readLabel = Yii::t('gvp', 'Read');
        $html .= Html::tag('span', substr($readLabel, 0, 1), [
            'class' => 'label label-' . (in_array(Status::ACTION_READ, $mask) ? "danger" : "default"),
            'title' => $readLabel, 'data-toggle' => 'tooltip'
        ]);
        $updateLabel = Yii::t('gvp', 'Update');
        $html .= Html::tag('span', substr($updateLabel, 0, 1), [
            'class' => 'label label-' . (in_array(Status::ACTION_UPDATE, $mask) ? "danger" : "default"),
            'title' => $updateLabel, 'data-toggle' => 'tooltip'
        ]);
        $deleteLabel = Yii::t('gvp', 'Delete');
        $html .= Html::tag('span', substr($deleteLabel, 0, 1), [
            'class' => 'label label-' . (in_array(Status::ACTION_DELETE, $mask) ? "danger" : "default"),
            'title' => $deleteLabel, 'data-toggle' => 'tooltip'
        ]);

        return $html;
    }
}
