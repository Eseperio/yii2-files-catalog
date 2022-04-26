<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\behaviors;


use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\Application as WebApplication;

/**
 * Class InfoBehavior
 * @package eseperio\filescatalog\behaviors
 */
class FilexBehavior extends Behavior
{

    use ModuleAwareTrait;

    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'setControlInfo',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'setControlInfo'
        ];
    }

    /**
     * @param $event ModelEvent
     * @throws \yii\base\InvalidConfigException
     */
    public function setControlInfo($event)
    {

        $owner = $this->owner;
        $user = null;

        if (Yii::$app instanceof WebApplication){
            $user = Yii::$app->get($owner->module->user);
        }

        $userId = ArrayHelper::getValue($user, $this->module->userIdAttribute);
        $userName = ArrayHelper::getValue($user, $this->module->userNameAttribute,Yii::t('filescatalog','System'));

        if ($event->name == BaseActiveRecord::EVENT_BEFORE_INSERT) {
            $owner->created_by = $userId;
            $owner->created_at = time();
            $owner->author_name = $userName;
        } else {
            $owner->updated_by = $userId;
            $owner->updated_at = time();
            $owner->editor_name = $userName;
        }
    }
}
