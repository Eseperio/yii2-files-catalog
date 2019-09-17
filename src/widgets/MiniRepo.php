<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use app\helpers\PolymorphicHelper;
use app\modules\repository\models\FilexItemFile;
use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\services\InodeHelper;
use yii\base\InvalidArgumentException;
use yii\base\Widget;

/**
 * Class MiniRepo
 * @package app\modules\repository\widgets
 */
class MiniRepo extends Widget
{

    /**
     * @var
     */
    public $remoteId;
    /**
     * @var
     */
    public $remoteType;

    /**
     * @var
     */
    public $uuid;


    /**
     * @var
     */
    private $dataProvider;

    /**
     * @var
     */
    private $model;

    /**
     * @var string
     */
    private $pjaxId = 'pjxmnirpo';

    /**
     * @throws \eseperio\filescatalog\exceptions\FilexAccessDeniedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\SecurityException
     */
    public function init()
    {
        if (empty($this->uuid) && (empty($this->remoteId)))
            throw new InvalidArgumentException('Uuid or remote id is required');

        if (!empty($this->uuid)) {
            $folder = Inode::find()->uuid($this->uuid)->onlyDirs()->one();
        } else {
            $remote = PolymorphicHelper::getRemoteRecord($this->remoteType, $this->remoteId, false);
            $folder = FilexItemFile::getObjectFolder($remote);
        }

        FileTypeIconsAsset::register($this->view);

        $this->model = $folder;
        $this->dataProvider = InodeHelper::getChildrenDataProvider($folder);
        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->render('mini', [
            'dataProvider' => $this->dataProvider,
            'model' => $this->model,
            'pjaxId' => $this->pjaxId,
        ]);
    }
}
