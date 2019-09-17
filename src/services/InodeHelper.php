<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\services;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\exceptions\FilexAccessDeniedException;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\Symlink;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class InodeHelper extends Component
{
    use ModuleAwareTrait;
    /**
     * @param $model Inode
     * @return ActiveDataProvider
     */
    public static function getChildrenDataProvider($model): ActiveDataProvider
    {
        $childrenQuery = $model->getChildren()
            ->with(['accessControlList'])
            ->excludeVersions()
            ->withSymlinksReferences()
            ->onlyReadable();
        $childrenQuery->orderBy([])->orderByType();

        if (self::getModule()->groupFilesByExt)
            $childrenQuery->orderByExtension();

        $childrenQuery->orderAZ();
        $dataProvider = new ActiveDataProvider([
            'query' => $childrenQuery,
            'key' => 'uuid',
            'pagination' => [
                'pageSize' => self::getModule()->itemsPerPage
            ]
        ]);

        return $dataProvider;
    }

    /**
     * Returns the model with the uuid specified. It uuid is null then root node is return.
     * @return array|Inode|\eseperio\filescatalog\models\Directory|\eseperio\filescatalog\models\File|\yii\db\ActiveRecord|\yii\web\Response|null
     * @throws FilexAccessDeniedException
     * @throws \yii\web\NotFoundHttpException
     */
    public static function getModel($uuid)
    {
        if (!empty($uuid)) {
            $model = self::findModel($uuid);
        } else {
            $model = Inode::find()
                ->onlyRoot()
                ->one();

            if (empty($model)) {
//                Root does not exists, create it
                $root = new Inode();
                $root->name = 'root';
                $root->type  =  InodeTypes::TYPE_DIR;
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
     * @return Inode|File|Directory
     * @throws NotFoundHttpException
     */
    public  static function findModel($id, $entity = Inode::class)
    {
        $query = call_user_func([$entity, 'find']);
        if (strlen($id) === 36) {
            $query->uuid($id);
        } else {
            $query->where(['id' => $id]);
        }

        $module = self::getModule();
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
