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
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\data\ActiveDataProvider;
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
        $dataProvider = $this->getChildrenDataProvider($model);

        $bulkActions = [
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

        return $this->controller->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'usePjax' => $this->module->usePjax,
            'parents' => $model->getParents()->asArray()->all(),
            'bulkActions' => $bulkActions
        ]);
    }

    /**
     * @return array|Inode|\eseperio\filescatalog\models\Directory|\eseperio\filescatalog\models\File|\yii\db\ActiveRecord|\yii\web\Response|null
     * @throws FilexAccessDeniedException
     * @throws \yii\web\NotFoundHttpException
     */
    private function getModel()
    {
        if ($uuid = Yii::$app->request->get('uuid', false)) {
            $model = $this->controller->findModel($uuid);
        } else {
            $model = Inode::find()
                ->onlyRoot()
                ->one();

            if (empty($model)) {
//                Root not exists, we create it
                $root = new Inode();
                $root->name = 'root';
                $root->makeRoot()->save(false);
                return $this->controller->redirect(Url::current());

            }

            if (!AclHelper::canRead($model))
                throw new FilexAccessDeniedException();
        }

        return $model;
    }

    /**
     * @param $model Inode
     * @return ActiveDataProvider
     */
    private function getChildrenDataProvider($model): ActiveDataProvider
    {
        $childrenQuery = $model->getChildren()
            ->with(['accessControlList'])
            ->excludeVersions()
            ->onlyReadable();
        $childrenQuery->orderBy([])->orderByType();

        if ($this->module->groupFilesByExt)
            $childrenQuery->orderByExtension();

        $childrenQuery->orderAZ();
        $dataProvider = new ActiveDataProvider([
            'query' => $childrenQuery,
            'key' => 'uuid',
            'pagination' => [
                'pageSize' => $this->module->itemsPerPage
            ]
        ]);

        return $dataProvider;
    }
}
