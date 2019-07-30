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

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_DELETE][] = 'type';
        $scenarios[self::SCENARIO_DEFAULT][] = 'type';

        return $scenarios;
    }

    public function rules()
    {
        $rules = array_merge_recursive(parent::rules(), [
            [['user_id', 'inode_id'], 'integer'],
            ['role', 'string'],
            [['inode_id'], 'required'],
            [['user_id'], 'required', 'when' => function ($model) {
                return $model->type == self::TYPE_USER;
            }],
            [['role'], 'required', 'when' => function ($model) {
                return $model->type == self::TYPE_ROLE;
            }],
            [['crud', 'type'], 'safe'],
            ['user_id', 'default', 'value' => self::DUMMY_USER],
            ['role', 'default', 'value' => self::DUMMY_ROLE],
        ]);

        return $rules;
    }

    public function beforeValidate()
    {
        if ($this->type == self::TYPE_USER && !empty($this->user_id))
            $this->role = self::DUMMY_ROLE;

        if ($this->type == self::TYPE_ROLE && !empty($this->role))
            $this->user_id = self::DUMMY_USER;

        return parent::beforeValidate();
    }


    public function init()
    {
        $this->type = self::TYPE_ROLE;
        $this->registerAssets();
        parent::init();
    }

    public function registerAssets()
    {
        $typeInputFormName = Html::getInputName($this, 'type');
        $userIdInputFormId = Html::getInputId($this, 'user_id');
        $roleInputFormId = Html::getInputId($this, 'role');
        $typeUser = self::TYPE_USER;
        $typeRole = self::TYPE_ROLE;
        $js = <<<JS
        document.getElementsByName('{$typeInputFormName}').forEach((e,i,a)=>{
        e.addEventListener('click',filexCheckType);
        });
        function filexCheckType(){
    let sel= document.querySelector('input[name="{$typeInputFormName}"]:checked').value == {$typeRole};
    document.querySelector('.field-{$userIdInputFormId}').classList.toggle('collapse',sel);
    document.querySelector('.filex-role-input').classList.toggle('collapse',!sel);
        }
        

JS;

        \Yii::$app->view->registerJs($js);
    }

}
