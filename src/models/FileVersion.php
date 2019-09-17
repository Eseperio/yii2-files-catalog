<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;

use eseperio\filescatalog\dictionaries\InodeTypes;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%fcatalog_inodes_version}}".
 *
 * @property int $file_id
 * @property int $version_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property File $original
 */
class FileVersion extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%fcatalog_inodes_version}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_id'], 'required'],
            [['file_id', 'version_id', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['file_id', 'version_id'], 'unique', 'targetAttribute' => ['file_id', 'version_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'file_id' => Yii::t('filescatalog', 'File ID'),
            'version_id' => Yii::t('filescatalog', 'Version ID'),
            'created_at' => Yii::t('filescatalog', 'Created At'),
            'updated_at' => Yii::t('filescatalog', 'Updated At'),
            'created_by' => Yii::t('filescatalog', 'Created By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOriginal()
    {
        return $this->hasOne(Inode::class, ['id' => 'file_id'])->andWhere(['type'=> InodeTypes::TYPE_FILE]);
    }

    public function behaviors()
    {
        return [
            'blameable' => [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class
            ]
        ];
    }
}
