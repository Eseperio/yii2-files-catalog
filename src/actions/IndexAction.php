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

        $parentId = 0;
        if ($uuid = Yii::$app->request->get('uuid', false)) {
            $model = $this->controller->findModel($uuid);
        } else {
            $model = Inode::find()
                ->onlyRoot()
                ->one();
            if (!AclHelper::canRead($model))
                throw new FilexAccessDeniedException();
        }

        if ($model->type !== InodeTypes::TYPE_DIR && !$model->isRoot())
            return $this->controller->redirect(['view', 'uuid' => $model->uuid]);

        Url::remember();
        $childrenQuery = $model->getChildren()
            ->excludeVersions()
        ->onlyAllowed();
        $childrenQuery->orderBy([])->orderByType();

        if ($this->module->groupFilesByExt)
            $childrenQuery->orderByExtension();

        $childrenQuery->orderAZ();
        $dataProvider = new ActiveDataProvider([
            'query' => $childrenQuery,
            'pagination' => [
                'pageSize' => $this->module->itemsPerPage
            ]
        ]);

        return $this->controller->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
            'usePjax' => $this->module->usePjax,
            'parents' => $model->getParents()->asArray()->all()
        ]);
    }
}
