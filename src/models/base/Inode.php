<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models\base;


use creocoder\nestedsets\NestedSetsBehavior;
use eseperio\filescatalog\FilesCatalogModule;
use eseperio\filescatalog\models\InodeQuery;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\UserException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * Class Inode
 * @package eseperio\filescatalog\models
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $extension
 * @property string $mime
 * @property string $md5hash
 * @property int $type
 * @property int $lft
 * @property int $rgt
 * @property int $depth
 * @property int $filesize
 * @property int $parent_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 *
 * Methods inherited from nested sets behavior:
 *
 * @method makeRoot($runValidation = true, $attributes = null)
 * @method prependTo($node, $runValidation = true, $attributes = null)
 * @method appendTo($node, $runValidation = true, $attributes = null)
 * @method insertBefore($node, $runValidation = true, $attributes = null)
 * @method insertAfter($node, $runValidation = true, $attributes = null)
 * @method deleteWithChildren()
 * @method InodeQuery parents($depth = null)
 * @method InodeQuery children($depth = null)
 * @method InodeQuery leaves()
 * @method InodeQuery prev()
 * @method InodeQuery next()
 * @method isRoot()
 * @method isChildOf($node)
 * @method isLeaf()
 * @method moveNodeAsRoot()
 * @method moveNode($value, $depth)
 * @method shiftLeftRightAttribute($value, $delta)
 * @method applyTreeAttributeCondition(&$condition)
 */
class Inode extends ActiveRecord
{

    use ModuleAwareTrait;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return static::getModule()->inodeTableName;
    }

    /**
     * @return InodeQuery|\yii\db\ActiveQuery
     */
    public static function find()
    {
        return new InodeQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'parent_id', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['name'], 'default', 'value' => Yii::t('xenon', 'New inode')],
            [['uuid'], 'string', 'max' => 36],
            [['uuid', 'name'], 'required'],
            ['extension', 'match', 'pattern' => '/[\w\d]+/'],
            [['extension'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 255],
            [['mime'], 'string', 'max' => 128],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('filescatalog', 'ID'),
            'uuid' => Yii::t('filescatalog', 'Uuid'),
            'name' => Yii::t('filescatalog', 'Name'),
            'extension' => Yii::t('filescatalog', 'Extension'),
            'mime' => Yii::t('filescatalog', 'Mime'),
            'type' => Yii::t('filescatalog', 'Type'),
            'parent_id' => Yii::t('filescatalog', 'Parent ID'),
            'created_at' => Yii::t('filescatalog', 'Created At'),
            'updated_at' => Yii::t('filescatalog', 'Updated At'),
            'created_by' => Yii::t('filescatalog', 'Created By'),
            'md5hash' => Yii::t('filescatalog', 'md5 Checksum'),
            'realPath'=>Yii::t('filescatalog','Real path'),
            'filesize'=>Yii::t('filescatalog','File size'),
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function beforeValidate()
    {
        if (empty($this->uuid))
            $this->uuid = (string)Uuid::uuid4();

        return parent::beforeValidate();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return array_replace_recursive($behaviors, [
            'tree' => [
                'class' => NestedSetsBehavior::class
            ],
            'slug' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'name',
                'attribute' => 'name'
            ],
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at'
            ],
            'blame' => [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
                'value' => function ($item) {
                    if (Yii::$app->has($this->module->user))
                        $userId = Yii::$app->get($this->module->user)->{$this->module->userIdAttribute};
                    if ($userId === null) {
                        return null;
                    }

                    return $userId;
                }
            ]
        ]);

    }

    /**
     * Prevent deletion of any item that has nested items
     * @return false|int
     * @throws UserException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete()
    {
        $children = $this->children()->count();
        if ($children > 0)
            throw new UserException(Yii::t('xenon', 'This item has nested items and cannot be deleted.'));

        return parent::delete();
    }

    /**
     * @return string with the real path where inode is supposed to be saved.
     */
    protected function getInodeRealPath()
    {
        if (is_callable($this->module->inodeRealPathCallback))
            return call_user_func($this->module->inodeRealPathCallback, $this);

        $path = "";
        if (!empty($this->module->directory))
            $path .= FileHelper::normalizePath($this->module->directory);

        $path .= DIRECTORY_SEPARATOR;

        $realFileNamesSystem = $this->module->realFileNamesSystem;
        if ($realFileNamesSystem == FilesCatalogModule::FILENAMES_BY_ID) {
            $path .= $this->idToPath() . DIRECTORY_SEPARATOR . $this->id;
        } else if ($realFileNamesSystem == FilesCatalogModule::FILENAMES_BY_UUID) {
            $path .= $this->uuidToPath() . DIRECTORY_SEPARATOR . $this->uuid;
        } else if ($realFileNamesSystem == FilesCatalogModule::FILENAMES_REAL) {
            $path .= $this->getRealPath() . DIRECTORY_SEPARATOR . $this->name . "." . $this->extension;
        }

        return $path;
    }

    /**
     * @return string
     */
    private function idToPath()
    {
        $pieces = str_split((string)$this->id);

        return join(DIRECTORY_SEPARATOR, $pieces);
    }

    /**
     * @return string
     */
    private function uuidToPath()
    {
        $cleanUuid = str_replace('-', '', $this->uuid);
        $pieces = str_split($cleanUuid, 2);

        return join(DIRECTORY_SEPARATOR, $pieces);
    }

    /**
     * @return string return the real path based on parents
     */
    public function getRealPath()
    {

        $path = join(DIRECTORY_SEPARATOR, $this->parents()->asArray()->select('name')->column());

        Yii::debug("EROS2 - " . nl2br(print_r($this->parents()->asArray()->column(), true)));

        return $path;
    }

}
