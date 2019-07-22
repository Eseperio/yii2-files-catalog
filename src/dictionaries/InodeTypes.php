<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\dictionaries;


use Yii;
use yii\base\InvalidArgumentException;

/**
 * Class InodeTypes
 * @package eseperio\filescatalog\dictionaries
 * @method static getName($typeId):string returns the name of the method
 */
class InodeTypes
{

    const TYPE_FILE = 1;
    const TYPE_DIR = 2;
    const TYPE_SYMLINK = 3;
    const TYPE_VERSION = 4;

    /**
     * @return array with all names of words
     */
    public static function getNames()
    {
        return [
            self::TYPE_FILE => Yii::t('filescatalog', 'File'),
            self::TYPE_DIR => Yii::t('filescatalog', 'Directory'),
            self::TYPE_SYMLINK => Yii::t('filescatalog', 'Symlink'),
            self::TYPE_VERSION => Yii::t('filescatalog', 'Version'),
        ];
    }

    /**
     * Indirect caller. This function allows retrieve a single record from a function that returns the whole array.
     * @param $name
     * @param $arguments
     * @return |null
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {

        $allowedMethods = static::getAllowedMethods();
        $keyword = substr($name, 3, strlen($name));
        if (substr($name, 0, 3) == 'get' && array_key_exists($keyword, $allowedMethods)) {
            $data = call_user_func([static::class, 'get' . ucfirst($allowedMethods[$keyword])]);
            if (is_array($arguments) && count($arguments) > 1)
                throw new InvalidArgumentException('Method only accepts one argument');

            $key = ltrim($arguments[0], "\\");
            if (array_key_exists($key, $data))
                return $data[$key];

            if (YII_ENV_DEV) {
                throw new InvalidArgumentException('Wrong type requested:  ' . $key);
            } else {
                return null;
            }
        }

        throw new \Exception('Method ' . __CLASS__ . "::" . $name . ' does not exist');
    }


    /**
     * @return array with the information of the available individual methods and the corresponding group function.
     *               ```
     *               [
     *                  'Name' => 'names' // that mean getName will try to extract data from getNames()
     *               ]
     *               ```
     */
    public static function getAllowedMethods()
    {
        return [
            'Name' => 'names'
        ];
    }
}
