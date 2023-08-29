<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\events\InodeEvent;
use Yii;
use yii\base\Event;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

/**
 * Class Inode
 * @package eseperio\filescatalog\models
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $extension
 * @property string $mime
 * @property int $type
 * @property int $parent_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $tenant_id
 * @property null|Inode[] $versions
 * @property Inode|null $original
 * @property-read mixed $fileVersions
 * @property-read string $contentAsBase64
 * @property-write resource $stream
 * @property-write mixed $asVersion
 * @property string $publicName
 */
class Inode extends \eseperio\filescatalog\models\base\Inode
{

    const EVENT_AFTER_INSERT_FILE = 'afterinsertfile';

    /**
     * Virtual property to store the amount of shares of a file. Must
     * be used in conjuntion with [[InodeQuery::withShares()]]. If not, false value
     * will be always returned.
     * @var bool|integer
     */
    public $shared = false;
    /**
     * @var UploadedFile
     */
    public $file;
    /**
     * @var bool whether file instance is a version
     */
    private $inodeType = InodeTypes::TYPE_DIR;
    /**
     * @var integer id of the original file. Used when creating a version
     */
    private $originalId;

    /**
     * @var resource to be saved manually. Only used when calling setStream()
     */
    private $_stream;

    /**
     * @param $stream resource defines the stream to be used when saving
     */
    public function setStream($stream)
    {
        if (in_array($this->type, [InodeTypes::TYPE_FILE, InodeTypes::TYPE_VERSION])) {
            $this->_stream = $stream;

            return $this;
        }

        throw new InvalidArgumentException(__METHOD__ . ' can only be used when type is file or version');

    }

    public function rules()
    {
        $rules = parent::rules();
        $skipEmptyFile = is_resource($this->_stream);
        switch ($this->type) {
            case InodeTypes::TYPE_FILE:
            case InodeTypes::TYPE_VERSION:
                $rules = array_replace_recursive($rules, [
                    ['file', 'file', 'skipOnEmpty' => $skipEmptyFile]
                ]);
                break;
        }

        return $rules;
    }

    public function setAsVersion($originalUuid)
    {
        $this->uuid = $originalUuid;
        $this->type = InodeTypes::TYPE_VERSION;
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function fileExists()
    {
        return $this->module->getStorageComponent()->has($this->getInodeRealPath());
    }

    public function beforeSave($insert)
    {
        switch ($this->type) {
            case InodeTypes::TYPE_DIR:
                $this->name = $this->getSafeFileName($this->name);
                break;
            case InodeTypes::TYPE_FILE:
            case InodeTypes::TYPE_VERSION:
                $this->beforeSaveFileInternal($insert);
                break;
        }

        return parent::beforeSave($insert);
    }

    /**
     * Returns a safe name for compatibility with many operative systems
     * @param UploadedFile|string $file
     * @return string
     */
    public function getSafeFileName($name): string
    {
        if ($name instanceof UploadedFile)
            $name = $name->baseName;

        return Inflector::slug($name, '_');
    }

    /**
     * @param $insert
     * @throws UserException
     */
    private function beforeSaveFileInternal($insert): void
    {
        if (!empty($this->uuid) && $insert) {
            $id = static::find()->where(['uuid' => $this->uuid])->select('id')->scalar();
            if (empty($id))
                throw new UserException(Yii::t('filescatalog', 'File not found'));
            $this->originalId = $id;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOriginal()
    {
        if ($this->type == InodeTypes::TYPE_DIR)
            throw new UserException(Yii::t('filescatalog', 'Directories does not accept versioning'));

        return $this->hasOne(Inode::class, ['id' => 'file_id'])
            ->viaTable('fcatalog_inodes_version', ['version_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersions()
    {
        return $this->hasMany(Inode::class, ['id' => 'version_id'])
            ->viaTable('fcatalog_inodes_version', ['file_id' => 'id'])
            ->andWhere(['type' => InodeTypes::TYPE_VERSION]);
    }

    public function getFileVersions()
    {
        return $this->hasMany(FileVersion::class, ['file_id' => 'id']);
    }

    /**
     * @return string the content of the file as base64 ready to be embedded.
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function getContentAsBase64()
    {
        $data = $this->getFile();

        return 'data:' . $this->mime . ';base64,' . base64_encode($data);
    }

    /**
     * @return bool|false|resource
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \yii\base\InvalidConfigException
     */
    public function getFile()
    {
        return $this->module->getStorageComponent()->read($this->getInodeRealPath());
    }

    public function afterDelete()
    {
        if ($this->module->enableACL) {
            $ids = $this->getDescendantsIds(null, true);
            $ids[] = $this->id;
            AccessControl::deleteAll(['inode_id' => $ids]);
        }
        Inode::deleteAll([
            'uuid' => $this->uuid,
            'type' => InodeTypes::TYPE_SYMLINK
        ]);
        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        switch ($this->type) {
            case InodeTypes::TYPE_FILE:
            case InodeTypes::TYPE_VERSION:
                $this->insertFileInternal($insert);
                break;
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @param $insert
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function insertFileInternal($insert): void
    {

        if ($insert) {
            try {
                $file = $this->file;

                if (is_resource($this->_stream)) {
                    $this->internalSaveStreamAsFile($this->_stream);
                } else if ($file instanceof UploadedFile && $this->validate(['file'])) {
                    $this->name = $this->getSafeFileName($file);
                    $this->mime = FileHelper::getMimeType($file->tempName);
                    $this->extension = mb_strtolower(Html::encode($file->extension));
                    $this->filesize = $file->size;
                    $fileStream = fopen($file->tempName, 'r+');
                    if ($this->module->checkFilesIntegrity)
                        $this->md5hash = hash_file('md5', $file->tempName);


                    $this->internalSaveStreamAsFile($fileStream);


                } else {

//                    throw new Exception('Attempt to create an empty or invalid file for: '. $this->name.".". $this->extension);
                    $this->delete();
                }
            } catch (\Throwable $e) {
                $this->addError('file',  Yii::t('filescatalog', "Error saving file content: ".$e->getMessage()));
                $this->delete();
                throw $e;
            }

            if ($this->type == InodeTypes::TYPE_VERSION && $insert) {
                $version = new FileVersion();
                $version->file_id = $this->originalId;
                $version->version_id = $this->id;
                if (!$version->save()) {
                    throw new Exception('Unable to save version.');
                }
            }


        }
    }

    /**
     * @param $fileStream
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    private function internalSaveStreamAsFile($fileStream): void
    {
        $filesystem = $this->module->getStorageComponent();
        $inodeRealPath = $this->getInodeRealPath();

        $parent = $this->getParent()->one();
        $siblingsNames = $parent->getChildren()
            ->onlyFiles()
            ->andWhere(['not', ['id' => $this->id]])
            ->asArray()
            ->select('name')
            ->column();

        if (in_array($this->name, $siblingsNames))
            $this->name = $this->getUniqueFilename($siblingsNames);


        $this->update(false);
        $this->validate();


        $method = "writeStream";

        if ($this->module->allowOverwrite && $filesystem->has($inodeRealPath))
            $method = "updateStream";


        if (!$filesystem->{$method}($inodeRealPath, $fileStream)) {
            $this->addError(Yii::t('filescatalog', 'Unable to move file to its destination'));
        }

        $event = Yii::createObject(Event::class);
        $this->trigger(self::EVENT_AFTER_INSERT_FILE, $event);
    }

    /**
     * @param array $siblingsNames
     * @return string
     */
    private function getUniqueFilename(array $siblingsNames)
    {
        $reclimit = 30;
        $counter = 0;
        $name = $this->name;
        while (in_array($name, $siblingsNames)) {
            if ($counter++ >= $reclimit)
                break;
            $lastDash = mb_strripos($name, '-');
            if ($lastDash) {
                $id = mb_substr($name, $lastDash + 1);
                if (is_numeric($id)) {
                    $name = mb_substr($name, 0, $lastDash + 1) . ++$id;
                }
            } else {
                $name = $name = $name . "-1";
            }
        }

        return $name;
    }

    /**
     * @return mixed
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function beforeDelete()
    {
        $response = parent::beforeDelete();

        if ($response) {
            switch ($this->type) {
                case InodeTypes::TYPE_FILE:
                case InodeTypes::TYPE_VERSION:
                    $response &= $this->deleteFileInternal();
                    break;
                case InodeTypes::TYPE_DIR:
                    $response &= $this->deleteDirInternal();
                    break;
            }
        }

        return $response;


    }

    /**
     * Deletes the real file associated with inode.
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @internal
     */
    private function deleteFileInternal(): bool
    {
        $response = true;
        try {
            $filesystem = $this->module->getStorageComponent();
            $realPath = $this->getInodeRealPath();

            if ($filesystem->has($realPath)) {
                $response &= $filesystem->delete($realPath);
                if (!$response)
                    throw new Exception('Unable to delete file');
            }
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());

            return false;
        }

        if ($this->type == InodeTypes::TYPE_VERSION) {
            FileVersion::deleteAll(['version_id' => $this->id]);
        } elseif ($this->type === InodeTypes::TYPE_FILE) {
            $versions = Inode::find()->where([
                'id' => ArrayHelper::getColumn($this->versions, 'id')
            ]);
            foreach ($versions->batch(100) as $rows) {
                foreach ($rows as $row) {
                    /* @var $row Inode */
                    $row->delete();
                }
            }
        }

        return $response;
    }

    /**
     * @internal
     * Deletes a directory
     */
    private function deleteDirInternal(): bool
    {
        try {
            if ($children = $this->getChildren()->all()) {
                foreach ($children as $k => $child) {
                    $deletions = $child->delete();
                    return $deletions;
                }
            }
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @return string the public name of the item. As an example, it ignores version filenames and use the main one
     */
    public function getPublicName(): string
    {
        switch ($this->type) {
            case InodeTypes::TYPE_VERSION:
                $name = $this->original->name;
                break;
            default:
                $name = $this->name;
                break;
        }

        return $name;
    }
}
