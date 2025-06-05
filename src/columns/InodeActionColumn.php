<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\grid\Column;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class InodeActionColumn extends Column
{
    use ModuleAwareTrait;

    /**
     * @var string param name of sort attribute. This value will be appended to view url for rendering next-previous links
     */
    public $sortParam = 'sort';


    /**
     * @inheritDoc
     * @throws \Throwable if debug is enabled and sortParam cannot be retrieved
     */
    public function init()
    {
        $this->sortParam = $this->getSortParam();
        parent::init();
    }

    /**
     * @return false|mixed
     * @throws \Throwable
     */
    protected function getSortParam()
    {
        try {
            return ArrayHelper::getValue($this, 'grid.dataProvider.sort.sortParam');
        } catch (\Throwable $e) {
            if (YII_DEBUG) {
                throw $e;
            }
        }
        return false;
    }

    /**
     * @param $index
     * @return float|int the row offset
     */
    public function getOffset($index)
    {
        $pagination = $this->grid->dataProvider->getPagination();
        if ($pagination !== false) {
            return $pagination->getOffset() + $index;
        }

        return $index + 1;

    }

    /**
     * @param Inode $model
     * @param mixed $key
     * @param int $index
     * @return string
     * @throws \Throwable
     */
    public function renderDataCellContent($model, $key, $index)
    {

        if (!AclHelper::canRead($model))
            return "";

        $result = $this->getMainButton($model, $index);
        $items = [];
        $this->addCommonActions($items, $model);

        if ($model->type == InodeTypes::TYPE_FILE) {
            $this->addFileActions($items, $model);
        } elseif ($model->type == InodeTypes::TYPE_DIR) {
            $this->addDirActions($items, $model);
        }
        $result .= Html::tag('ul', join('', $items), ['class' => 'dropdown-menu dropdown-menu-right']);

        return Html::tag('div', $result, ['class' => 'btn-group pull-right', 'style' => 'display: flex']);
    }

    /**
     * @param $items
     * @param $model
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function addFileActions(&$items, $model)
    {
        $recentVersion = $model;
        if ($this->module->allowVersioning && !empty($model->versions)) {
            $versions = $model->versions;
            $recentVersion = end($versions);
        }
        $items[] = Html::tag(
            'li',
            Html::a(Yii::t('filescatalog', 'Download'), ['download', 'uuid' => $recentVersion->uuid],
                [
                    'class' => 'dropdown-item',
                    'data-pjax' => 0
                ]
            )
        );

        if ($this->module->enableEmailSharing && AclHelper::canShare($model)) {
            $items[] = Html::tag(
                'li',
                Html::a(Yii::t('filescatalog', 'Share via email'), ['email', 'uuid' => $recentVersion->uuid],
                    [
                        'class' => 'dropdown-item',
                        'data-pjax' => 0
                    ]
                )
            );
        }


    }

    /**
     * @param $items
     * @param $model
     * @return void
     */
    public function addDirActions(&$items, $model)
    {


    }

    /**
     * @param $items
     * @param $model
     * @return void
     */
    public function addCommonActions(&$items, $model)
    {
        $propertiesUrl = ['properties', 'uuid' => $model->uuid];
        if ($model->type == InodeTypes::TYPE_SYMLINK) {
            $propertiesUrl['created_at'] = $model->created_at;
        }
        $items[] = Html::tag(
            'li',
            Html::a(Yii::t('filescatalog', 'Properties'), $propertiesUrl,
                [
                    'class' => 'dropdown-item',
                    'data-pjax' => 0
                ]
            )
        );

        if ($this->module->allowRenaming && AclHelper::canWrite($model)) {
            $renameUrl = ['rename', 'uuid' => $model->uuid];
            if ($model->type == InodeTypes::TYPE_SYMLINK) {
                $renameUrl['created_at'] = $model->created_at;
            }
            $items[] = Html::tag(
                'li',
                Html::a(Yii::t('filescatalog', 'Rename'), $renameUrl,
                    [
                        'class' => 'dropdown-item',
                        'data-pjax' => 0
                    ]
                )
            );
        }

        if ($this->module->allowMoving && AclHelper::canWrite($model)) {
            $moveUrl = ['move', 'uuid' => $model->uuid];
            // Symlinks cannot be moved
            if ($model->type !== InodeTypes::TYPE_SYMLINK) {
                $items[] = Html::tag(
                    'li',
                    Html::a(Yii::t('filescatalog', 'Move'), $moveUrl,
                        [
                            'class' => 'dropdown-item',
                            'data-pjax' => 0
                        ]
                    )
                );
            }
        }

        if ($this->module->enableUserSharing && AclHelper::canShare($model)) {
            $items[] = Html::tag(
                'li',
                Html::a(Yii::t('filescatalog', 'Share with a user'), ['share', 'uuid' => $model->uuid],
                    [
                        'class' => 'dropdown-item',
                        'data-pjax' => 0
                    ]
                )
            );
        }


    }

    /**
     * @param \eseperio\filescatalog\models\Inode $model
     * @param int $index
     * @return string
     * @throws \Throwable
     */
    protected function getMainButton(Inode $model, int $index): string
    {
        if ($model->type == InodeTypes::TYPE_DIR || ($model->type == InodeTypes::TYPE_SYMLINK && $model->symlink_type == InodeTypes::TYPE_DIR)) {
            $action = 'index';
            $label = Yii::t('filescatalog', 'Open');
            $url = [
                $action,
            ];
        } else {
            $label = Yii::t('filescatalog', 'View');
            $action = 'view';
            $url = [
                $action,
                $this->module->sortParam => Yii::$app->request->get($this->getSortParam()),
                $this->module->offsetParam => $this->getOffset($index),
            ];
        }

        $url['uuid'] = $model->uuid;

        $result = Html::a($label, $url, [
            'class' => 'btn btn-default btn-sm',
            'data-pjax' => 0
        ]);

        $result .= Html::button(
            Html::tag('span', '', ['class' => 'caret']) .
            Html::tag('span', 'Toggle Dropdown', ['class' => 'sr-only']), [
                'class' => 'btn btn-default btn-sm dropdown-toggle',
                'data-toggle' => 'dropdown',
                'aria-haspopup' => 'true',
                'aria-expanded' => 'false',
            ]
        );
        return $result;
    }
}
