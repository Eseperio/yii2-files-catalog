<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\controllers;


use eseperio\filescatalog\actions\DeleteAction;
use eseperio\filescatalog\actions\DownloadAction;
use eseperio\filescatalog\actions\FakeAction;
use eseperio\filescatalog\actions\IndexAction;
use eseperio\filescatalog\actions\NewFolderAction;
use eseperio\filescatalog\actions\PropertiesAction;
use eseperio\filescatalog\actions\RemoveACL;
use eseperio\filescatalog\actions\UploadAction;
use eseperio\filescatalog\actions\ViewAction;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\exceptions\FilexAccessDeniedException;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\Symlink;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class DefaultController extends \yii\web\Controller
{
    use ModuleAwareTrait;


    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'properties', 'download'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['upload', 'new-folder', 'remove-acl', 'delete'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['fake'],
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'remove-acl' => ['post'],
                    'upload' => ['post'],
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (\Yii::$app->request->isGet)
            Url::remember();

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => ['class' => IndexAction::class],
            'upload' => ['class' => UploadAction::class],
            'new-folder' => ['class' => NewFolderAction::class],
            'properties' => ['class' => PropertiesAction::class],
            'view' => ['class' => ViewAction::class],
            'download' => ['class' => DownloadAction::class],
            'delete' => ['class' => DeleteAction::class],
            'remove-acl' => ['class' => RemoveACL::class],
            'fake' => ['class' => FakeAction::class]
        ];
    }

    /**
     * @inheritdoc Additionaly adds module to all views in order to have configuration params
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render($view, $params = [])
    {
        \yii\helpers\ArrayHelper::setValue($params, 'filexModule', $this->module);

        return parent::render($view, $params);
    }

    /**
     * @param $id
     * @param string $entity
     * @return Inode|File|Directory
     * @throws NotFoundHttpException
     */
    public function findModel($id, $entity = Inode::class)
    {
        $query = call_user_func([$entity, 'find']);
        if (strlen($id) === 36) {
            $query->uuid($id)->one();
        } else {
            $query->where(['id' => $id]);
        }

        $module = $this->module;
        if ($module->enableACL)
            $query->with(['accessControlList']);
        /* @var $model Inode|File|Symlink */
        if (($model = $query->one()) == null)
            throw new NotFoundHttpException();

        if ($module->enableACL) {
            $aclModel = ($model->type === InodeTypes::TYPE_VERSION) ? $model->original : $model;
            if (!AclHelper::canRead($aclModel))
                throw new FilexAccessDeniedException();
        }


        return $model;
    }
}
