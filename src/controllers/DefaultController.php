<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\controllers;


use eseperio\filescatalog\actions\BulkAcl;
use eseperio\filescatalog\actions\BulkCut;
use eseperio\filescatalog\actions\BulkDelete;
use eseperio\filescatalog\actions\BulkDownload;
use eseperio\filescatalog\actions\CutAction;
use eseperio\filescatalog\actions\CutFilesAction;
use eseperio\filescatalog\actions\DeleteAction;
use eseperio\filescatalog\actions\DirectoryTreeLoadAction;
use eseperio\filescatalog\actions\DownloadAction;
use eseperio\filescatalog\actions\FakeAction;
use eseperio\filescatalog\actions\IndexAction;
use eseperio\filescatalog\actions\InheritAcl;
use eseperio\filescatalog\actions\NewFolderAction;
use eseperio\filescatalog\actions\NewLinkAction;
use eseperio\filescatalog\actions\MoveAction;
use eseperio\filescatalog\actions\PropertiesAction;
use eseperio\filescatalog\actions\RemoveACL;
use eseperio\filescatalog\actions\RemoveShare;
use eseperio\filescatalog\actions\RenameAction;
use eseperio\filescatalog\actions\SharedWithMe;
use eseperio\filescatalog\actions\ShareViaEmail;
use eseperio\filescatalog\actions\ShareWithUser;
use eseperio\filescatalog\actions\UploadAction;
use eseperio\filescatalog\actions\ViewAction;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\services\InodeHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
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
                        'actions' => ['index', 'view', 'properties', 'download', 'bulk-download', 'shared', 'directory-tree-load', 'cut-files'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['upload', 'new-folder', 'delete', 'bulk-delete', 'bulk-cut', 'cut', 'new-link', 'rename', 'move', 'email', 'share', 'unshare'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['remove-acl', 'bulk-acl', 'inherit-acl'],
                        'matchCallback' => function ($rule, $action) {
                            $filexModule = $this->module;
                            return $filexModule->enableACL && $filexModule->isAdmin();
                        }
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
                    'bulk-delete' => ['post'],
                    'bulk-cut' => ['post'],
                    'cut' => ['post'],
                    'bulk-acl' => ['post'],
                    'inherit-acl' => ['post'],
                    'bulk-download' => ['post'],
                    'cut-files' => ['post', 'get'],
                    'unshare' => ['post'],
                    'move' => ['post', 'get'],
                ],
            ],
        ];
    }

    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {

        if (Yii::$app->request->isGet) {
            $previous = Yii::$app->user->getReturnUrl();
            $current = Url::to();
            if ($previous !== $current) {
                Yii::$app->user->setReturnUrl($previous);
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        $actions = [
            'index' => ['class' => IndexAction::class],
            'upload' => ['class' => UploadAction::class],
            'rename' => ['class' => RenameAction::class],
            'move' => ['class' => MoveAction::class],
            'new-folder' => ['class' => NewFolderAction::class],
            'new-link' => ['class' => NewLinkAction::class],
            'properties' => ['class' => PropertiesAction::class],
            'view' => ['class' => ViewAction::class],
            'download' => ['class' => DownloadAction::class],
            'delete' => ['class' => DeleteAction::class],
            'bulk-delete' => ['class' => BulkDelete::class],
            'bulk-cut' => ['class' => BulkCut::class],
            'cut' => ['class' => CutAction::class],
            'bulk-acl' => ['class' => BulkAcl::class],
            'remove-acl' => ['class' => RemoveACL::class],
            'inherit-acl' => ['class' => InheritAcl::class],
            'fake' => ['class' => FakeAction::class],
            'bulk-download' => ['class' => BulkDownload::class],
            'cut-files' => ['class' => CutFilesAction::class],
            'shared' => ['class' => SharedWithMe::class],
            'unshare' => ['class' => SharedWithMe::class],
            'directory-tree-load' => ['class' => DirectoryTreeLoadAction::class],
        ];
        if ($this->getModule()->enableEmailSharing) {
            $actions['email'] = ['class' => ShareViaEmail::class];
        }
        if ($this->getModule()->enableUserSharing) {
            $actions['share'] = ['class' => ShareWithUser::class];
            $actions['unshare'] = ['class' => RemoveShare::class];
        }
        return $actions;

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
     * @return Inode|Directory
     * @throws NotFoundHttpException
     */
    public function findModel($id, $createdAt = null)
    {
        return InodeHelper::findModel($id, $createdAt);
    }
}
