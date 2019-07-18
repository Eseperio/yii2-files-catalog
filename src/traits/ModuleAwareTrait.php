<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
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
