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
     * Returns the model with the uuid specified. It uuid is null then root node is returned.
     * If root node does not exist, it will be created.
     * @param null $uuid
     * @param bool $checkAccess whether perform read permissions check before return
     * @return array|Inode|\eseperio\filescatalog\models\Directory|\yii\db\ActiveRecord|\yii\web\Response|null
     * @throws FilexAccessDeniedException
     * @throws NotFoundHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getModel($uuid = null, $checkAccess = true)
    {
        if (!empty($uuid)) {
            $model = self::findModel($uuid);
        } else {
            $model = Inode::find()
                ->onlyRoot()
                ->one();
            if (empty($model)) {
//                Root does not exist, create it
                $root = \Yii::createObject([
                    'class' => Inode::class
                ]);
                $root->name = 'root';
                $root->type = InodeTypes::TYPE_DIR;
                $root->makeRoot()->save(false);


                $model = $root;
            }


        }
        if ($checkAccess && !AclHelper::canRead($model))
            throw new FilexAccessDeniedException();
        return $model;
    }

    /**
     * Searchs the requested model and checks if user has read rights
     * @param $id
     * @param null $created_at if provided then symlink will be returned
     * @return Inode|Directory
     * @throws FilexAccessDeniedException
     * @throws NotFoundHttpException
     */
    public static function findModel($id, $created_at = null)
    {
        $query = Inode::find();
        if (strlen($id) === 36) {
            $query->uuid($id);
        } else {
            $query->where(['id' => $id]);
        }

        if (!empty($created_at) && preg_match('/\d{10}/', $created_at)) {
            $query->andWhere(['created_at' => $created_at]);
        }

        $module = self::getModule();
        if ($module->enableACL) {
            $query->with(['accessControlList']);
        }
        $query->withShares();

        /* @var $model Inode|Symlink */
        if (($model = $query->one()) == null)
            throw new NotFoundHttpException();

        if ($module->enableACL) {
            $aclModel = ($model->type === InodeTypes::TYPE_VERSION) ? $model->original : $model;
            if (!AclHelper::canRead($aclModel)){
                throw new FilexAccessDeniedException();
            }
        }

        return $model;
    }

    /**
     * Creates a link for the specified inode within the specified folder.
     * User must have writing permissions on target folder
     * ATTENTION: This method will not check access control.
     * @param Inode $inode
     * @param Inode $folder
     * @param null $permissions
     * @return false|Inode false if error, Inode model if success. Returns inode since 1.3.3
     * @throws \yii\base\InvalidConfigException
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

            return $symLink;
        }

        return false;
    }
}
