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
use eseperio\filescatalog\models\base\Inode;
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
 * @property File[] $versions
 * @property File $original
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


                    $parent = $this->getParent()->one();
                    $siblingsNames = $parent->getChildren()
                        ->onlyFiles()
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


                    if (!$filesystem->{$method}($inodeRealPath, $tmpFile)) {
                        $this->addError(Yii::t('filescatalog', 'Unable to move file to its destination'));
                    }


                } else {
                    $this->delete();
                }
            } catch (\Throwable $e) {
                $this->addError('file', Yii::t('filescatalog', $e->getMessage()));
                $this->delete();
                throw $e;
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
     * @param bool $ensureRealDeletion whether throw an error when realfile can not be deleted
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete()
    {
        try {
            $filesystem = $this->module->getStorageComponent();
            $realPath = $this->getInodeRealPath();

            if ($filesystem->has($realPath)) {
                $filesystem->delete($realPath);
            }
        } catch (\Throwable $e) {
            Yii::error($e->getMessage());
        }

        if ($this->type == InodeTypes::TYPE_VERSION)
            FileVersion::deleteAll(['version_id' => $this->id]);

        parent::delete();
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
            ->viaTable('fcatalog_inodes_version', ['file_id' => 'id'])
            ->andWhere(['type' => InodeTypes::TYPE_VERSION]);
    }

    public function getFileVersions()
    {
        return $this->hasMany(FileVersion::class, ['file_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOriginal()
    {
        return $this->hasOne(File::class, ['id' => 'file_id'])
            ->viaTable('fcatalog_inodes_version', ['version_id' => 'id']);
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
