<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\traits;


use Yii;
use yii\helpers\ArrayHelper;

trait ContainerStaticHelper
{

    /**
     * Returns the name of the class overrided in container.
     * @param $className
     * @return mixed
     */
    public static function getContainerClass($className)
    {
        $defs = Yii::$container->definitions;
        if (array_key_exists($className, $defs)) {
            $className = ArrayHelper::getValue($defs, $className . ".class", $defs[$className]);
        }

        return $className;

    }
}
