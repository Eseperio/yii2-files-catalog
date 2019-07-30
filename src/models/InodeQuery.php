<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use app\helpers\ArrayHelper;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use paulzi\adjacencyList\AdjacencyListQueryTrait;
use Yii;
use yii\db\ActiveQuery;

/**
 * Class InodeQuery
 * @package eseperio\filescatalog\models
 * @method $this roots() gets the roots nodes
 */
class InodeQuery extends ActiveQuery
{
    use ModuleAwareTrait;
    use AdjacencyListQueryTrait;

    /**
     * Filters only root files
     * @return InodeQuery
     */
    public function onlyRoot()
    {
        return $this->roots()->limit(1);
    }

    public function excludeVersions()
    {
        return $this->andWhere(['!=', 'type', InodeTypes::TYPE_VERSION]);
    }

    public function onlyAllowed()
    {
        $authManager = Yii::$app->authManager;
        $userRoles = ArrayHelper::getColumn($authManager->getRolesByUser($this->module->getUserId()), 'name');

        $allRoles = [];
        foreach ($userRoles as $role) {
            $allRoles = $allRoles + ArrayHelper::getColumn($authManager->getChildRoles($role), 'name');
        }

        return $this;
    }

    /**
     * @param $uuid
     * @return InodeQuery
     */
    public function uuid($uuid)
    {
        return $this->andWhere(['uuid' => $uuid]);
    }

    public function orderByExtension($order = SORT_DESC)
    {
        return $this->addOrderBy([
            'extension' => $order
        ]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderByType($order = SORT_DESC)
    {
        return $this->addOrderBy([
            'type' => $order,
        ]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderAZ($order = SORT_ASC)
    {
        return $this->addOrderBy([
            'name' => $order
        ]);
    }

    /**
     * @return InodeQuery
     */
    public function onlySymlinks()
    {
        return $this->andWhere([
            'type' => InodeTypes::TYPE_SYMLINK
        ]);
    }

    /**
     * @return InodeQuery
     */
    public function onlyFiles()
    {
        return $this->andWhere([
            'type' => InodeTypes::TYPE_FILE
        ]);
    }


    /**
     * @return InodeQuery
     */
    public function onlyDirs()
    {
        return $this->andWhere([
            'type' => InodeTypes::TYPE_DIR
        ]);
    }
}
