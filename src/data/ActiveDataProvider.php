<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\data;


class ActiveDataProvider extends \yii\data\ActiveDataProvider
{
    /**
     * Set the main key to uuid, so no real id exposed in listview widgets
     * @var string
     */
    public $key = 'uuid';
}
