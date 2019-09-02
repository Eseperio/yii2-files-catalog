<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\Symlink;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Widget;

class Breadcrumb extends Widget
{
    use ModuleAwareTrait;
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
        $parents = $this->model->getParents()->all();
        $newFolderLabel = Yii::t('filescatalog', 'New folder');
        $propertiesLabel = Yii::t('filescatalog', 'Properties');
        $addFilesLabel = Yii::t('filescatalog', 'Add files');
        $linkLabel = Yii::t('filescatalog', 'Add link');


        return $this->render('breadcrumb', [
            'model' => $this->model,
            'parents' => $parents,
            'pjaxId' => $this->pjaxId,
            'showPropertiesBtn' => $this->showPropertiesBtn,
            'showLabels' => $this->module->showBreadcrumbButtonLabels,
            'newFolderLabel' => $newFolderLabel,
            'propertiesLabel' => $propertiesLabel,
            'linkLabel' => $linkLabel,
            'addFilesLabel' => $addFilesLabel,
            'newFolderIcon' => Html::tag('i', '', ['class' => $this->module->newFolderIconclass]),
            'propertiesIcon' => Html::tag('i', '', ['class' => $this->module->propertiesIconClass]),
            'linkIcon' => Html::tag('i', '', ['class' => $this->module->linkIconClass]),


        ]);
    }
}
