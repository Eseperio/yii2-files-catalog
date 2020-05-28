<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\exceptions\FilexAccessDeniedException;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeSearch;
use eseperio\filescatalog\services\InodeHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\helpers\Url;
use yii\web\Controller;

class IndexAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    /**
     * @return string
     */
    public function run()
    {

        $model = $this->getModel();

        if ($model->type !== InodeTypes::TYPE_DIR && !$model->isRoot())
            return $this->controller->redirect(['view', 'uuid' => $model->uuid]);

        Url::remember();
        $bulkActions = $this->getBulkActions();


        $searchModel = Yii::createObject(InodeSearch::class);
        $searchModel->uuid = Yii::$app->request->get('uuid');
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $this->getModel());

        return $this->controller->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'usePjax' => $this->module->usePjax,
            'parents' => $model->getParents()->asArray()->all(),
            'bulkActions' => $bulkActions
        ]);
    }

    /**
     * @return array|Inode|\eseperio\filescatalog\models\Directory|\yii\db\ActiveRecord|\yii\web\Response|null
     * @throws FilexAccessDeniedException
     * @throws \yii\web\NotFoundHttpException
     */
    protected function getModel()
    {
        return InodeHelper::getModel(Yii::$app->request->get('uuid', false));
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
                    'data' => [
                        'method' => 'post',
                        'params' => json_encode([]),

                    ]
                ]
            ],
            [
                'label' => Yii::t('filescatalog', 'Delete'),
                'url' => ['/filex/default/bulk-delete'],
                'linkOptions' => [
                    'id' => 'filex-bulk-delete',
                    'class' => 'text-danger',
                    'data' => [
                        'method' => 'post',
                        'params' => json_encode([]),

                    ]
                ]
            ],
        ];

        if ($this->module->isAdmin())
            $bulkActions[] = [
                'label' => Yii::t('filescatalog', 'Add permission'),
                'url' => ['/filex/default/bulk-acl'],
                'linkOptions' => [
                    'id' => 'filex-bulk-acl',
                    'class' => 'text-danger',
                    'data' => [
                        'method' => 'post',
                        'params' => json_encode([]),

                    ]
                ]

            ];

        return $bulkActions;
    }
}
