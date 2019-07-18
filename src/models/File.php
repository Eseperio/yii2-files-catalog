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

namespace eseperio\filescatalog\models;

use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

/**
 * Class File
 * @package eseperio\filescatalog\models
 */
class File extends Inode
{
    use ModuleAwareTrait;

    /**
     * @var
     */
    public $file;


    /**
     * @return array
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(), [
            ['file', 'file', 'skipOnEmpty' => false]
        ]);
    }

    /**
     * @return int
     */
    public function getInodeType()
    {
        return InodeTypes::TYPE_FILE;
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
                    $this->name = Inflector::slug($uploadedFile->name);
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


                    if ($filesystem
                        ->{$method}($inodeRealPath, $tmpFile, [

                        ])) {
                        return true;
                    } else {
                        $this->addError(Yii::t('filescatalog', 'Unable to move file to its destination'));
                    }

                } else {
                    $this->delete();
                }
            } catch (\Throwable $e) {
                $this->addError('file', Yii::t('filescatalog', $e->getMessage()));
                $this->delete();
            }

        }

        parent::afterSave($insert, $changedAttributes);
    }
}
