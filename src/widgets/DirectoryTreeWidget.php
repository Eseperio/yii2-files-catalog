<?php

namespace eseperio\filescatalog\widgets;

use eseperio\filescatalog\assets\DirectoryTreeWidgetAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\services\InodeHelper;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * DirectoryTreeWidget displays a directory tree and allows selection of directories and/or files.
 * 
 * Works with a virtual filesystem based on inodes instead of actual filesystem directories.
 * It can be configured to show only directories, only files, or both.
 * It supports single or multiple selection modes.
 */
class DirectoryTreeWidget extends InputWidget
{
    /**
     * Show only directories
     */
    const MODE_DIRECTORIES_ONLY = 1;

    /**
     * Show both directories and files
     */
    const MODE_ALL = 2;

    /**
     * @var string|null The UUID of the root directory inode to start from (null for system root)
     */
    public $rootNodeUuid = null;

    /**
     * @var int The mode of elements to show (directories only or all)
     */
    public $mode = self::MODE_ALL;

    /**
     * @var array File extensions to show (e.g. ['jpg', 'png', 'pdf'])
     */
    public $extensions = [];

    /**
     * @var bool Whether to allow multiple selection
     */
    public $multiple = false;

    /**
     * @var array HTML options for the container
     */
    public $containerOptions = ['class' => 'directory-tree-widget'];

    /**
     * @var string The URL to the action that will handle AJAX requests
     */
    public $ajaxUrl;

    /**
     * @var array list of UUIDs to exclude from the tree
     */
    public $excludedUuids = [];

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        if ($this->ajaxUrl === null) {
            $this->ajaxUrl = Url::to(['/filex/default/directory-tree-load']);
        }

        $this->registerClientScript();
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $content = $this->renderWidget();

        // Create the hidden input field that will store the selected value(s)
        $input = $this->hasModel()
            ? Html::activeHiddenInput($this->model, $this->attribute, $this->options)
            : Html::hiddenInput($this->name, $this->value, $this->options);

        return Html::tag('div', $content . $input, $this->containerOptions);
    }

    /**
     * Renders the widget content
     * 
     * @return string the rendered content
     */
    protected function renderWidget()
    {
        return $this->render('directory-tree', [
            'widget' => $this,
            'id' => $this->options['id'],
        ]);
    }

    /**
     * Registers the required JavaScript
     */
    protected function registerClientScript()
    {
        $view = $this->getView();
        DirectoryTreeWidgetAsset::register($view);

        // Traducciones para que puedan ser detectadas por la herramienta de extracción de mensajes
        $i18n = [
            'loading' => Yii::t('filescatalog', 'Loading...'),
            'emptyDirectory' => Yii::t('filescatalog', 'Empty directory'),
            'errorLoading' => Yii::t('filescatalog', 'Error loading directory'),
        ];

        $options = Json::encode([
            'id' => $this->options['id'],
            'ajaxUrl' => $this->ajaxUrl,
            'multiple' => $this->multiple,
            'mode' => $this->mode,
            'extensions' => $this->extensions,
            'rootNodeUuid' => $this->rootNodeUuid,
            'excludedUuids' => $this->excludedUuids,
            'i18n' => $i18n,
        ]);

        $view->registerJs("$('#{$this->options['id']}-container').directoryTree($options);");
    }
}
