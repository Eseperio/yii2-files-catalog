<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use creocoder\flysystem\ZipArchiveFilesystem;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class BulkDownload
 * @package eseperio\filescatalog\actions
 */
class BulkDownload extends Bulk
{

    /**
     * @var ZipArchiveFilesystem
     */
    private $zipAdapter;
    private $tmpFile;
    private $errors = [];

    private $maxIterations = 500;
    private $curIter = 0;

    /**
     * @var array with all directories uuids to prevent infinite recursion
     */
    private $dirsIncluded = [];

    /**
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function run()
    {
        $this->initZipAdapter();
        $this->pack($this->getModels());
        if (count($this->errors))
            throw new InvalidConfigException('Errors during packing: ' . implode(' | ', $this->errors));
        $this->zipAdapter->getAdapter()->getArchive()->close();
        \Yii::$app->response->sendFile($this->tmpFile, 'bulk-download.zip');
    }

    /**
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function initZipAdapter()
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'blk');
        $this->zipAdapter = \Yii::createObject([
            'class' => ZipArchiveFilesystem::class,
            'path' => $this->tmpFile
        ]);

        return true;
    }

    /**
     * Packs file into a temporary zip file
     * @param $models
     * @throws \Throwable
     */
    protected function pack($models, $path = '.')
    {
        if (++$this->curIter > $this->maxIterations) {
            $this->errors = [Yii::t('xenon', 'Max amount of files reached')];

            return;
        }
        try {
            foreach ($models as $model) {
                /* @var $model Inode */
                switch ($model->type) {
                    case InodeTypes::TYPE_DIR:
                        $currentPath = $path . DIRECTORY_SEPARATOR . $model->name;
                        if (in_array($model->uuid, $this->dirsIncluded)) {
                            break;
                        } else {
                            $this->dirsIncluded[] = $model->uuid;
                        }
                        $this->zipAdapter->createDir($currentPath);
                        $children = $model->getChildren();
                        foreach ($children->batch(50) as $filesGroup) {
                            if (!is_iterable($filesGroup))
                                throw new Exception('filesgroup');
                            $this->pack($filesGroup, $currentPath);
                        }
                        break;
                    case InodeTypes::TYPE_FILE:
                        $this->zipAdapter->writeStream($path . DIRECTORY_SEPARATOR . $model->name . "." . $model->extension, $model->getStream());
                        break;
                    case InodeTypes::TYPE_SYMLINK:
                        $realModel = Inode::find()
                            ->where(['!=', 'type', InodeTypes::TYPE_SYMLINK])
                            ->uuid($model->uuid)
                            ->one();
                        $this->pack([$realModel], $path);
                        break;
                }
            }

        } catch (\Throwable $e) {
            $this->errors[] = $e->getMessage() . " - Path: " . $path;
        }
    }

    public function __destruct()
    {
        @unlink($this->tmpFile);
    }

    /**
     * @return \eseperio\filescatalog\models\InodeQuery|\yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ForbiddenHttpException
     */
    protected function getModelsQuery()
    {
        return parent::getModelsQuery()->onlyReadable();
    }


}
