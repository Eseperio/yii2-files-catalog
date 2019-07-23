<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;

use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Exception;
use yii\base\UserException;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

/**
 * Class File
 * @package eseperio\filescatalog\models
 * @property FileVersion[] $versions
 */
class File extends Inode
{
    use ModuleAwareTrait;

    /**
     * @var UploadedFile
     */
    public $file;
    /**
     * @var bool whether file instance is a version
     */
    private $inodeType = InodeTypes::TYPE_FILE;
    /**
     * @var integer id of the original file. Used when creating a version
     */
    private $originalId;

    /**
     * @return array
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(), [
            ['file', 'file', 'skipOnEmpty' => false]
        ]);
    }

    public function beforeSave($insert)
    {
        if (!empty($this->uuid) && $insert) {
            $id = File::find()->where(['uuid' => $this->uuid])->select('id')->scalar();
            if (empty($id))
                throw new UserException(Yii::t('filescatalog', 'File not found'));
            $this->originalId = $id;
        }

        return parent::beforeSave($insert);
    }

    public function setAsVersion($originalUuid)
    {
        $this->uuid = $originalUuid;
        $this->inodeType = InodeTypes::TYPE_VERSION;
    }

    /**
     * @return int
     */
    public function getInodeType()
    {
        return $this->inodeType;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @return bool|void
     * @throws \Throwable
     * @throws \yii\base\UserException
     * @throws \yii\db\StaleObjectException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            try {
                $uploadedFile = $this->file;
                if ($uploadedFile instanceof UploadedFile && $this->validate(['file'])) {
                    $this->name = Inflector::slug($uploadedFile->baseName);
                    $this->mime = FileHelper::getMimeType($uploadedFile->tempName);
                    $this->extension = mb_strtolower(Html::encode($uploadedFile->extension));
                    $this->filesize = $uploadedFile->size;
                    $filesystem = $this->module->getStorageComponent();
                    $tmpFile = fopen($uploadedFile->tempName, 'r+');
                    $inodeRealPath = $this->getInodeRealPath();
                    if ($this->module->checkFilesIntegrity)
                        $this->md5hash = hash_file('md5', $uploadedFile->tempName);

                    $this->update(false);
                    $this->validate();

                    $method = "writeStream";

                    if ($this->module->allowOverwrite && $filesystem->has($inodeRealPath))
                        $method = "updateStream";


                    if (!$filesystem->{$method}($inodeRealPath, $tmpFile)) {
                        $this->addError(Yii::t('filescatalog', 'Unable to move file to its destination'));
                    }

                } else {
                    $this->delete();
                }
            } catch (\Throwable $e) {
                $this->addError('file', Yii::t('filescatalog', $e->getMessage()));
                $this->delete();
            }

            if ($this->inodeType == InodeTypes::TYPE_VERSION && $insert) {
                $version = new FileVersion();
                $version->file_id = $this->originalId;
                $version->version_id = $this->id;
                if (!$version->save()) {
                    throw new Exception('Unable to save version.');
                }
            }

        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function fileExists()
    {
        return $this->module->getStorageComponent()->has($this->getInodeRealPath());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersions()
    {
        return $this->hasMany(File::class, ['id' => 'version_id'])
            ->viaTable('fcatalog_inodes_version', ['file_id' => 'id']);
//        return $this->hasMany(FileVersion::class, ['file_id' => 'id']);
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
}
