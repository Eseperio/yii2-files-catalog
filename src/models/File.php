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
use eseperio\filescatalog\models\Inode;
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
class File extends \eseperio\filescatalog\models\Inode
{
    use ModuleAwareTrait;














}
