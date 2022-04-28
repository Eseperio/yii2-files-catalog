<?php

namespace eseperio\filescatalog\models\base;


use eseperio\bootstrap\Html;
use eseperio\filescatalog\behaviors\FilexBehavior;
use eseperio\filescatalog\traits\InodeRelationTrait;
use Yii;
use yii\validators\DateValidator;

/**
 * This is the base model class for table "{{%fcatalog_shares}}".
 *
 * @property integer $inode_id
 * @property integer $user_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $expires_at
 * @property \eseperio\filescatalog\models\Inode $inode
 */
class InodeShare extends \yii\db\ActiveRecord
{

    use InodeRelationTrait;
    public $set_expiring_date = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $expiresAtInputId = Html::getInputId($this, 'set_expiring_date');
        return [
            [['inode_id', 'user_id'], 'integer'],
            ['user_id', 'required'],
            ['set_expiring_date', 'boolean'],
            [
                'expires_at',
                'required',
                'when' => function ($model) {
                    return (bool)$model['set_expiring_date'];
                },
                'whenClient' => <<<JS
function(){
    return $('#{$expiresAtInputId}').is(':checked')
}
JS
            ],
            [
                'expires_at',
                'date',
                'type' => DateValidator::TYPE_DATE,
                'min' => strtotime('+10minutes'),
                'minString' => Yii::t('filescatalog', 'tomorrow'),
                'format' => 'yyyy-MM-dd',
                'timestampAttribute' => 'expires_at'
            ],
            [
                'user_id', 'unique',
                'targetAttribute' => ['user_id', 'inode_id'],
                'filter' => [
                    'OR',
                    ['>', 'expires_at', time()],
                    ['expires_at' => null]
                ],
                'message' => Yii::t('filescatalog', 'This item has already shared with this user')
            ]
        ];
    }



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fcatalog_shares}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'inode_id' => Yii::t('filescatalog', 'Inode ID'),
            'user_id' => Yii::t('filescatalog', 'User ID'),
            'expires_at' => Yii::t('filescatalog', 'Expires At'),
        ];
    }

    /**
     * @inheritdoc
     * @return array mixed
     */
    public function behaviors()
    {
        return [
            'filex' => [
                'class' => FilexBehavior::class,
            ],
        ];
    }


}
