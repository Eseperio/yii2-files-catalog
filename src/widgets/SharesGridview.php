<?php

namespace eseperio\filescatalog\widgets;

use Yii;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\i18n\Formatter;

/**
 *
 */
class SharesGridview extends GridView
{

    /**
     * @var string[]
     */
    public $tableOptions = ['class' => 'table'];
    /**
     * @var string[]
     */
    public $formatter = [
        'class' => Formatter::class,
        'nullDisplay' => '--'
    ];

    /**
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->loadColumns();
        parent::init();
    }

    /**
     * @return void
     */
    protected function loadColumns()
    {
        $this->columns = [
            'user_id' => 'user_id',
            'expires_at' => [
                'attribute' => 'expires_at',
                'content' => function ($inodeShare, $key, $index, $column) {
                    /* @var $inodeShare \eseperio\filescatalog\models\InodeShare */
                    /* @var $column \yii\grid\Column */

                    $html = $column->grid->formatter->asDate($inodeShare->expires_at);
                    if (!empty($inodeShare->expires_at) && $inodeShare->expires_at < time()) {
                        $html .= " " . Html::tag('span', "(" . Yii::t('filescatalog', 'expired') . ")", ['class' => 'text-red']);
                    }
                    return $html;
                }
            ],
            'actions' => [
                'content' => function ($inodeShare) {
                    /* @var $inodeShare \eseperio\filescatalog\models\InodeShare */
                    return \yii\helpers\Html::a(Yii::t('filescatalog', 'Delete'), ['unshare'], [
                        'data' => [
                            'method' => 'post',
                            'params' => [
                                'uuid' => $inodeShare->inode->uuid,
                                'user_id' => $inodeShare->user_id
                            ]
                        ]
                    ]);
                }
            ]
        ];
    }
}
