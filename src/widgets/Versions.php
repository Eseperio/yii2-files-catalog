<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use app\helpers\ArrayHelper;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\File;
use yii\base\InvalidArgumentException;
use yii\base\Widget;

class Versions extends Widget
{
    /**
     * @var File
     */
    public $model;

    public function run()
    {
        if (empty($this->model) || !$this->model instanceof File)
            throw new InvalidArgumentException(__CLASS__ . "::model must be defined and be instance of " . File::class);

        if ($this->model->type === InodeTypes::TYPE_VERSION) {
            $original = $this->model->original;
        } else {
            $original = $this->model;
        }
        if (empty($original))
            throw new InvalidArgumentException('Unable to get original model');

        $versions = $original->versions;

        $isLast = false;
        $dates = ArrayHelper::getColumn($versions, 'created_at');
        if (end($dates) === $this->model->created_at)
            $isLast = true;

        return $this->render('versions', [
            'versions' => $versions,
            'model' => $this->model,
            'isLast' => $isLast,
            'lastVersion'=> end($versions)
        ]);
    }
}
