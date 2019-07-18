<?php
/**
 * Copyright (c) 2019. Grupo Smart (Spain)
 *
 * This software is protected under Spanish law. Any distribution of this software
 * will be prosecuted.
 *
 * Developed by Waizabú <code@waizabu.com>
 * Updated by: erosdelalamo on 18/7/2019
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
     * @var Inode|Directory|File|Symlink
     */
    public $model;

    public function run()
    {

        $parents= $this->model->parents()->asArray()->all();
        return $this->render('breadcrumb', [
            'model' => $this->model,
            'parents'=>$parents
        ]);
    }
}
