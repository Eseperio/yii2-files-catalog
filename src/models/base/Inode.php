<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models\base;


use app\components\StringHelper;
use eseperio\filescatalog\behaviors\FilexBehavior;
use eseperio\filescatalog\FilesCatalogModule;
use eseperio\filescatalog\helpers\Helper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\InodeQuery;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use paulzi\adjacencyList\AdjacencyListBehavior;
use Ramsey\Uuid\Uuid;
use Yii;
use yii\base\UserException;
use yii\behaviors\SluggableBehavior;
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
 * @property int $updated_by
 * @property string $author_name
 * @property string $editor_name
 * @property string $deleteHash
 * @property AccessControl[] $accessControlList
 *
 * Methods inherited from nested sets behavior:
 * @property string $humanName
 * @method  events()
 * @method  attach($owner)
 * @method  InodeQuery getParents($depth = null)
 * @method  getParentsOrdered($depth = null)
 * @method  InodeQuery getParent()
 * @method  InodeQuery getRoot()
 * @method  getDescendants($depth = null, $andSelf = false)
 * @method  getDescendantsOrdered($depth = null)
 * @method  InodeQuery getChildren()
 * @method  InodeQuery getLeaves($depth = null)
 * @method  getPrev()
 * @method  getNext()
 * @method  getParentsIds($depth = null, $cache = true)
 * @method  getDescendantsIds($depth = null, $flat = false, $cache = true)
 * @method  populateTree($depth = null)
 * @method  isRoot()
 * @method  isChildOf($node)
 * @method  isLeaf()
 * @method  $this makeRoot()
 * @method  $this prependTo($node)
 * @method  $this appendTo($node)
 * @method  $this insertBefore($node)
 * @method  $this insertAfter($node)
 * @method  preDeleteWithChildren()
 * @method  deleteWithChildren()
 * @method  reorderChildren($middle = true)
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
            [['name'], 'default', 'value' => Yii::t('filescatalog', 'New inode')],
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
            'uuid' => Yii::t('filescatalog', 'ID'),
            'name' => Yii::t('filescatalog', 'Name'),
            'extension' => Yii::t('filescatalog', 'Extension'),
            'mime' => Yii::t('filescatalog', 'Mime'),
            'type' => Yii::t('filescatalog', 'Type'),
            'parent_id' => Yii::t('filescatalog', 'Parent ID'),
            'created_at' => Yii::t('filescatalog', 'Created At'),
            'updated_at' => Yii::t('filescatalog', 'Updated At'),
            'created_by' => Yii::t('filescatalog', 'Created By'),
            'md5hash' => Yii::t('filescatalog', 'MD5 Checksum'),
            'realPath' => Yii::t('filescatalog', 'Real path'),
            'filesize' => Yii::t('filescatalog', 'File size'),
            'humanName' => Yii::t('filescatalog', 'Human name'),
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert)
            $this->uuid = (string)Uuid::uuid4();

        return parent::beforeSave($insert);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return array_replace_recursive($behaviors, [
            'adjacency' => [
                'class' => AdjacencyListBehavior::class,
                'sortable' => false
            ],
            'slug' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'name',
                'attribute' => 'name'
            ],
            'filex' => [
                'class' => FilexBehavior::class
            ]
        ]);

    }


    public function beforeDelete()
    {
        $children = $this->getChildren()->count();
        if ($children > 0)
            throw new UserException(Yii::t('filescatalog', 'This item has nested items and cannot be deleted.'));

        return parent::beforeDelete();
    }

    /**
     * @return bool|false|resource
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function getStream()
    {
        return $this->module->getStorageComponent()->readStream($this->getInodeRealPath());
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
        $path = join(DIRECTORY_SEPARATOR, $this->getParents()->asArray()->select('name')->column());

        return $path;
    }

    /**
     * @return string
     */
    public function getHumanName($maxLenght = null)
    {

        $humanized = Helper::humanize($this->name);
        if (!empty($maxLenght))
            $humanized = StringHelper::truncate($humanized, $maxLenght);

        return $humanized;
    }

    public function getAccessControlList()
    {
        return $this->hasMany(AccessControl::class, ['inode_id' => 'id']);
    }

    /**
     * @return string the hash required to confirm deletion
     */
    public function getDeleteHash()
    {
        return $this->getSecureHash('delete');
    }

    /**
     * Returns a secure hash for an specific action. This must be used to check whether a request
     * is valid for this file.
     * @param $action
     * @return string
     * @internal
     */
    protected function getSecureHash($action)
    {
        return hash('SHA3-256', $this->id . $action . $this->module->salt . $this->uuid);
    }
}
