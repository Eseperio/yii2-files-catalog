<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog;


use eseperio\filescatalog\models\File;
use League\Flysystem\Filesystem;
use Yii;
use yii\base\Module;
use yii\validators\FileValidator;

class FilesCatalogModule extends Module
{

    const FILENAMES_BY_UUID = 1;
    const FILENAMES_BY_ID = 2;
    const FILENAMES_REAL = 3;
    public $controllerNamespace = "eseperio\\filescatalog\\controllers";
    /**
     * @var string Name of the table where inode are gonna be stored
     */
    public $inodeTableName = "fcatalog_inodes";
    /**
     * @var int the maximum number of bytes required for the uploaded file.
     * Defaults to null, meaning no limit.
     * Note, the size limit is also affected by `upload_max_filesize` and `post_max_size` INI setting
     * and the 'MAX_FILE_SIZE' hidden field value. See [[FileValidator::getSizeLimit()]] for details.
     * @see https://secure.php.net/manual/en/ini.core.php#ini.upload-max-filesize
     * @see https://secure.php.net/post-max-size
     * @see FileValidator::getSizeLimit
     */
    public $maxFileSize = null;
    /**
     * @var string This will be used as default directory where all files will be created. Set to false to use your
     *      default storage component
     */
    public $directory = 'filex';
    /**
     * @var int number of maximun versions of a files that can be kept.
     */
    public $maxVersions = 4;
    /**
     * @var string name of the component responsible of handling files. Requires flysystem.
     */
    public $storage = 'storage';
    /**
     * @var string The model to be used fot files
     */
    public $fileModel = File::class;
    /**
     * @var string The user component.
     */
    public $user = 'user';

    public $userIdAttribute = 'id';
    /**
     * @var string Name of the db component to use on data handling
     */
    public $db = 'db';
    /**
     * @var bool whether use pjax on main view
     */
    public $usePjax = true;
    /**
     * @var null|array|\Closure Callable used to bypass current inodeRealPath calculation
     */
    public $inodeRealPathCallback = null;
    /**
     * @var bool whether overwrite existing files. Remember this setting can be overrided in calls tu save
     */
    public $allowOverwrite = false;
    /**
     * @var string the prefix to be used on urlGroup
     */
    public $prefix = 'filex';
    /**
     * @var array the url rules (routes)
     */
    public $urlRules = [
        '<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>'
    ];
    /**
     * @var int the max amount of elements to display when using a tree view. Set to false to disable
     */
    public $maxTreeDepthDisplay = 4;
    /**
     * @var bool whether show icons grouped by extension
     */
    public $groupFilesByExt = false;
    /**
     * @var string the prefix for the route part of every rule declared in [[rules]].
     * The prefix and the route will be separated with a slash.
     * If this property is not set, it will take the value of [[prefix]].
     */
    public $routePrefix = "filesCatalog";
    /**
     * @var string which kind of name use on saving files.
     *             Defaults to FILENAMES_BY_ID. Files will be stored using its own id, so an
     *             attacker can not find a file based on their public uuid.
     *             If you want to preserve an easy way to find physical
     *             FILENAMES_BY_ID: File 1979 will become prefix/1/9/7/9/1979
     *             FILENAMES_BY_UUID: File 146d8c31-ca60-411f-b112-7dd1bc5e8e46
     *             will become
     *             prefix/14/6d/8c/31/ca/60/41/1f/b1/12/7d/d1/bc/5e/8e/46/146d8c31-ca60-411f-b112-7dd1bc5e8e46
     *             FILENAMES_REAL will create parent directories with the name of the parent virtual directories.
     */
    public $realFileNamesSystem = self::FILENAMES_BY_ID;
    /**
     * @var array list of the mimetypes that can be represented directly in browser with their corresponding tag
     */
    public $browserInlineMimeTypes = [
        'image/jpeg' => 'img',
        'image/png' => 'img',
        'image/gif' => 'img',
        'image/svg+xml' => 'img',
        'image/webp' => 'img',
        'image/x-icon' => 'img',
        'application/pdf' => 'iframe',
        'audio/mpeg' => 'audio',
        'audio/ogg' => 'audio',
        'audio/wav' => 'audio',
        'video/mp4' => 'video',
        'video/webm' => 'video',
        'video/ogg' => 'video',

    ];
    /**
     * Since this module relies on Flysystem, you can not have a direct link to the file, so in order to preview
     * images or mp4 videos they are converted to base64. This number limits the maximun size allowed for a file to be
     * embedded.
     * @var int max inline file size in bytes. Defaults to 10Mb
     */
    public $maxInlineFileSize = 10000000;
    /**
     * @var bool whether save file hashes in database and check integrity everytime a file is required.
     *           In large filesystems it can make the database grow significantly.
     */
    public $checkFilesIntegrity = true;

    /**
     * @var bool whether allow multiple versions of a file.
     */
    public $allowVersioning = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->registerTranslations();
        parent::init();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['filescatalog'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => __DIR__ . DIRECTORY_SEPARATOR . 'messages',
        ];
    }

    /**
     * @return object|Filesystem
     * @throws \yii\base\InvalidConfigException
     */
    public function getStorageComponent()
    {
        $storage = \Yii::$app->get($this->storage);

        return $storage;
    }
}
