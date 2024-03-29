<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use yii\helpers\Html;
use Yii;

/**
 * Class InodePermissionsForm
 * @package eseperio\filescatalog\models
 * @property $custom_role
 * @property $type
 */
class InodePermissionsForm extends AccessControl
{
    const CUSTOM_ROLE_VALUE = -1;
    const CUSTOM_ROLE_GROUP_ID = 'custom-role-group';
    /**
     * @var string
     */
    public $custom_role;
    /**
     * @var integer
     */
    public $type;

    /**
     * @return array
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios[self::SCENARIO_DELETE][] = 'type';
        $scenarios[self::SCENARIO_DEFAULT][] = 'type';
        $scenarios[self::SCENARIO_DEFAULT][] = 'custom_role';

        return $scenarios;
    }

    /**
     * @return array
     */
    public function rules()
    {
        $rules = array_merge_recursive(parent::rules(), [
            [['user_id', 'inode_id', 'crud_mask'], 'integer'],
            ['role', 'string'],
            ['custom_role', 'string'],
            [['inode_id'], 'required'],
            [['user_id'], 'required', 'when' => function ($model) {
                return $model->type == self::TYPE_USER;
            }],
            [['role'], 'required', 'when' => function ($model) {
                return $model->type == self::TYPE_ROLE;
            }],
            [['custom_role'], 'required',
                'when' => function ($model) {
                    return $model->type == self::TYPE_ROLE && $model->role == self::CUSTOM_ROLE_VALUE;
                }
            ],
            ['user_id', 'default', 'value' => self::DUMMY_USER],
            ['role', 'default', 'value' => self::DUMMY_ROLE],
        ]);

        return $rules;
    }

    public function attributeLabels()
    {
        return array_merge_recursive(parent::attributeLabels(), [
            'type' => Yii::t('filescatalog', 'Type'),
            'custom_role' => Yii::t('filescatalog', 'Custom role'),
        ]);
    }

    public function beforeSave($insert)
    {
        if ($this->type == self::TYPE_ROLE && $this->role == self::CUSTOM_ROLE_VALUE) {
            $this->role = $this->custom_role;
        }


        return parent::beforeSave($insert); 
    }

    /**
     * @return mixed
     */
    public function beforeValidate()
    {
        if ($this->scenario != self::SCENARIO_DELETE) {
            if ($this->type == self::TYPE_USER && !empty($this->user_id))
                $this->role = self::DUMMY_ROLE;

            if ($this->type == self::TYPE_ROLE && !empty($this->role))
                $this->user_id = self::DUMMY_USER;
        }

        return parent::beforeValidate();
    }


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->type = self::TYPE_ROLE;
        if (!Yii::$app instanceof \yii\console\Application) {
            $this->registerAssets();
        }
        parent::init();
    }

    /**
     * Register js functionality when form is rendered
     */
    public function registerAssets()
    {
        $filexModule = self::getModule();
        $availablePermissions = $filexModule->getAclPermissions();
        $typeInputFormName = Html::getInputName($this, 'type');
        $userIdInputFormId = Html::getInputId($this, 'user_id');
        $customRoleId = Html::getInputId($this, 'custom_role');
        $roleSelector = Html::getInputId($this, 'role');
        $customRoleGroupId = self::CUSTOM_ROLE_GROUP_ID;
        $customRoleVal = self::CUSTOM_ROLE_VALUE;

        $typeRole = self::TYPE_ROLE;
        $js = <<<JS
        (function() {
            filexCheckType();
            document.getElementById('$roleSelector').addEventListener("change", checkCustomRole);
        })();

        function checkCustomRole(){
            let elm = document.getElementById('$roleSelector');
    
            let val = elm.value;
            console.warn('val');
            console.log(val);
            let customRoleGroup = document.getElementById('$customRoleGroupId');
                 
            if(val == $customRoleVal){
                customRoleGroup.classList.remove('collapse');
            }else{
                customRoleGroup.classList.add('collapse');
            }
        }    
        document.getElementsByName('{$typeInputFormName}').forEach(function(e,i,a){
        e.addEventListener('click',filexCheckType);
        });
        function filexCheckType(){
            let sel = document.querySelector('input[name="{$typeInputFormName}"]:checked').value == {$typeRole};
            document.querySelector('.field-{$userIdInputFormId}').classList.toggle('collapse',sel);
            document.querySelector('.filex-role-input').classList.toggle('collapse',!sel);
        }
        

JS;

        \Yii::$app->view->registerJs($js);
    }

}
