<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\services;


use eseperio\filescatalog\data\ActiveDataProvider;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\exceptions\FilexAccessDeniedException;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\AccessControl;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\Symlink;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;

class InodeHelper extends Component
{
    use ModuleAwareTrait;

    /**
     * @param $model     Inode
     * @param $onlyFiles bool whether filter only files and symlinks
     * @return ActiveDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public static function getChildrenDataProvider($model, $onlyFiles = false): ActiveDataProvider
    {
        $childrenQuery = $model->getChildren()
            ->with(['accessControlList'])
            ->excludeVersions()
            ->withSymlinksReferences()
            ->onlyReadable();

        if ($onlyFiles)
            $childrenQuery
                ->byType([InodeTypes::TYPE_SYMLINK, InodeTypes::TYPE_FILE]);

        $childrenQuery->orderBy([])->orderByType();

        if (self::getModule()->groupFilesByExt)
            $childrenQuery->orderByExtension();

        $childrenQuery->orderAZ();
        $dataProvider = new ActiveDataProvider([
            'query' => $childrenQuery,
            'pagination' => [
                'pageSize' => self::getModule()->itemsPerPage
            ]
        ]);

        return $dataProvider;
    }

    /**
     * Returns the model with the uuid specified. It uuid is null then root node is return.
     * @return array|Inode|\eseperio\filescatalog\models\Directory|\yii\db\ActiveRecord|\yii\web\Response|null
     * @throws FilexAccessDeniedException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function getModel($uuid = null)
    {
        if (!empty($uuid)) {
            $model = self::findModel($uuid);
        } else {
            $model = Inode::find()
                ->onlyRoot()
                ->one();

            if (empty($model)) {
//                Root does not exists, create it
                $root = \Yii::createObject([
                    'class' => Inode::class
                ]);
                $root->name = 'root';
                $root->type = InodeTypes::TYPE_DIR;
                $root->makeRoot()->save(false);

                return $root;

            }

            if (!AclHelper::canRead($model))
                throw new FilexAccessDeniedException();
        }

        return $model;
    }

    /**
     * @param $id
     * @param string $entity
     * @return Inode|Directory
     * @throws NotFoundHttpException
     */
    public static function findModel($id)
    {
        $query = Inode::find();
        if (strlen($id) === 36) {
            $query->uuid($id);
        } else {
            $query->where(['id' => $id]);
        }

        $module = self::getModule();
        if ($module->enableACL)
            $query->with(['accessControlList']);
        /* @var $model Inode|Symlink */
        if (($model = $query->one()) == null)
            throw new NotFoundHttpException();

        if ($module->enableACL) {
            $aclModel = ($model->type === InodeTypes::TYPE_VERSION) ? $model->original : $model;
            if (!AclHelper::canRead($aclModel))
                throw new FilexAccessDeniedException();
        }

        return $model;
    }

    /**
     * Creates a link for the specified inode within the specified folder.
     * User must have writing permissions on target folder
     * ATTENTION: This method will not check access control.
     * @param Inode $inode
     * @param Inode $folder
     */
    public static function linkToInode(Inode $inode, Inode $folder, $permissions = null)
    {

        $symLink = \Yii::createObject([
            'class' => Inode::class
        ]);
        $symLink->type = InodeTypes::TYPE_SYMLINK;
        $symLink->uuid = $inode->uuid;
        $symLink->name = $inode->name;
        if ($symLink->appendTo($folder)->save()) {
            AccessControl::grantAccessToUsers($symLink, Yii::$app->user, $permissions);

            return true;
        }

        return false;
    }
}
