<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\admintheme\helpers\Html;

class InodePermissionsForm extends AccessControl
{

    public $type;


    public function rules()
    {
        return [
            [['item_id', 'inode_id'], 'integer'],
            [['item_id', 'inode_id'], 'required'],
            [['crud', 'type'], 'safe'],
        ];
    }

    public function beforeSave($insert)
    {
        if ($this->type == self::TYPE_USER && !empty($this->user_id))
            $this->role = null;

        if ($this->type == self::TYPE_ROLE && !empty($this->role))
            $this->user_id = null;
    }

    public function init()
    {
        $this->type = self::TYPE_USER;
        $this->registerAssets();
        parent::init();
    }

    public function registerAssets()
    {
        $typeInputFormName = Html::getInputName($this, 'type');
        $userIdInputFormId = Html::getInputId($this, 'user_id');
        $RoleInputFormId = Html::getInputId($this, 'role');
        $typeUser = self::TYPE_USER;
        $typeRole = self::TYPE_ROLE;
        $js = <<<JS
        
        function filexCheckType(){
    let sel= document.querySelector('input[name="{$typeInputFormName}"]:checked').value;
    document.getElementsByName()
        }
        

JS;

        \Yii::$app->view->registerJs($js);
    }

}
