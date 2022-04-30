<?php

namespace eseperio\filescatalog\widgets;

use app\helpers\ArrayHelper;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Renders the shares basic information.
 * IMPORTANT: Do not forget to load the model calling `InodeQuery::withShares()`.
 * Otherwise, this widget will never display
 * @see \eseperio\filescatalog\models\InodeQuery::withShares()
 */
class SharedWith extends Widget
{
    use ModuleAwareTrait;

    /**
     * Remember
     * @var \eseperio\filescatalog\models\Inode;
     */
    public $model;

    /**
     * @var array options for the label generated.
     * You can change the tag rendered using `tag` option
     */
    public $options = ['class' => 'text-muted'];

    /**
     * @return string
     */
    public function run()
    {
        if (empty($this->model)) {
            throw new InvalidConfigException(__CLASS__ . '::$model must be defined');
        }

        if (!$this->module->enableUserSharing || !$this->model->shared || !AclHelper::canShare($this->model)) {
            return "";
        }
        return $this->getSharedLabel();
    }

    /**
     * Render the shared label
     * @return string
     */
    protected function getSharedLabel(): string
    {
        $msg = Yii::t('filescatalog', 'Shared with {n,plural,=0{nobody} =1{a user} other{# users}}', ['n' => $this->model->shared]);

        $tag = ArrayHelper::remove($this->options, 'tag', 'div');
        return Html::tag($tag, $msg, $this->options);
    }
}
