<?php

namespace eseperio\filescatalog\models\base;



use eseperio\filescatalog\behaviors\FilexBehavior;
use Yii;

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
 */
class InodeShare extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['inode_id', 'user_id', 'expires_at'], 'integer']
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
