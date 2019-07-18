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

namespace eseperio\filescatalog\traits;

use eseperio\filescatalog\FilesCatalogModule;
use Yii;

/**
 * @property-read FilesCatalogModule $module
 */
trait ModuleAwareTrait
{
    /**
     * @return FilesCatalogModule
     */
    public static function getModule()
    {
        return Yii::$app->getModule('filex');
    }
}
