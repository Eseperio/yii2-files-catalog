<?php

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\data\ActiveDataProvider;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;

/**
 *
 */
class SharedWithMe extends Action
{
    use ModuleAwareTrait;
    /**
     * @return string
     */
    public function run()
    {
        $bulkActions = $this->getBulkActions();

        $dataProvider = $this->getDataProvider();
        return $this->controller->render('shared-with-me', [
            'dataProvider' => $dataProvider,
            'usePjax' => $this->module->usePjax,
            'bulkActions'=> $this->getBulkActions()

        ]);
    }

    /**
     * @return \eseperio\filescatalog\data\ActiveDataProvider
     */
    protected function getDataProvider()
    {
        $query = Inode::find()
            ->sharedWithMe()
            ->onlyDirs();

        return new ActiveDataProvider([
            'query' => $query,

        ]);
    }

    /**
     * @return array
     */
    protected function getBulkActions(): array
    {
        $bulkActions = [
            [
                'label' => Yii::t('filescatalog', 'Download'),
                'url' => ['/filex/default/bulk-download'],
                'linkOptions' => [
                    'id' => 'filex-bulk-download',
                    'class'=> 'filex-bulk-delete',
                    'data' => [
                        'method' => 'post',
                        'params' => json_encode([]),

                    ]
                ]
            ],
        ];

        if ($this->module->isAdmin() && false)
            $bulkActions[] = [
                'label' => Yii::t('filescatalog', 'Add permission'),
                'url' => ['/filex/default/bulk-acl'],
                'linkOptions' => [
                    'id' => 'filex-bulk-acl',
                    'class' => 'text-danger filex-bulk-delete' ,
                    'data' => [
                        'method' => 'post',
                        'params' => json_encode([]),

                    ]
                ]

            ];

        return $bulkActions;
    }

}
