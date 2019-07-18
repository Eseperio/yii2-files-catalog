<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\Symlink;
use yii\base\Widget;

class Breadcrumb extends Widget
{
    /**
     * @var string used only when pjax is enabled
     */
    public $pjaxId;
    /**
     * @var Inode|Directory|File|Symlink
     */
    public $model;

    /**
     * @var bool whether display properties button
     */
    public $showPropertiesBtn = true;

    public function run()
    {

        $parents = $this->model->parents()->asArray()->all();

        return $this->render('breadcrumb', [
            'model' => $this->model,
            'parents' => $parents,
            'pjaxId' => $this->pjaxId,
            'showPropertiesBtn' => $this->showPropertiesBtn
        ]);
    }
}
