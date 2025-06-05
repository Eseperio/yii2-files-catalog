<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use yii\helpers\Html;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\Symlink;
use eseperio\filescatalog\services\CutPasteService;
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
     * @var Inode|Directory|Symlink
     */
    public $model;

    /**
     * @var bool whether display properties button
     */
    public $showPropertiesBtn = true;

    public function run()
    {
        $parentsQuery = $this->model->getParents();
        if ($this->module->enableACL)
            $parentsQuery->with('accessControlList');

        $parents = $parentsQuery->all();
        $newFolderLabel = Yii::t('filescatalog', 'New folder');
        $propertiesLabel = Yii::t('filescatalog', 'Properties');
        $addFilesLabel = Yii::t('filescatalog', 'Add files');
        $linkLabel = Yii::t('filescatalog', 'Add link');
        $pasteLabel = Yii::t('filescatalog', 'Paste');

        // Get the cut paste service
        $service = Yii::createObject(CutPasteService::class);

        // Check if there are cut inodes
        $hasCutInodes = $service->hasCutInodes();

        // Check if user has write permissions for the current directory
        $canPaste = $hasCutInodes && $this->model->type === \eseperio\filescatalog\dictionaries\InodeTypes::TYPE_DIR && 
                    \eseperio\filescatalog\helpers\AclHelper::canWrite($this->model);

        return $this->render('breadcrumb', [
            'model' => $this->model,
            'filexModule'=> $this->module,
            'parents' => $parents,
            'pjaxId' => $this->pjaxId,
            'showPropertiesBtn' => $this->showPropertiesBtn,
            'showLabels' => $this->module->showBreadcrumbButtonLabels,
            'newFolderLabel' => $newFolderLabel,
            'propertiesLabel' => $propertiesLabel,
            'linkLabel' => $linkLabel,
            'addFilesLabel' => $addFilesLabel,
            'pasteLabel' => $pasteLabel,
            'hasCutInodes' => $hasCutInodes,
            'canPaste' => $canPaste,
            'newFolderIcon' => Html::tag('i', '', ['class' => $this->module->newFolderIconclass]),
            'propertiesIcon' => Html::tag('i', '', ['class' => $this->module->propertiesIconClass]),
            'linkIcon' => Html::tag('i', '', ['class' => $this->module->linkIconClass]),
            'pasteIcon' => Html::tag('i', '', ['class' => $this->module->pasteIconClass]),
        ]);
    }
}
