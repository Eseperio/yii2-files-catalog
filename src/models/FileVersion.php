<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;

use Yii;
use yii\base\UserException;

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
            [['file_id', 'version_id'], 'required'],
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

    public function beforeSave($insert)
    {
        $original = $this->original;

        if (empty($original))
            throw new UserException(Yii::t('filescatalog', 'Unable to add version'));

        $max = FileVersion::find()->where(['file_id' => $original->id])->max('version_id');
        $this->version_id = empty($max) ? 2 : $max;

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOriginal()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
