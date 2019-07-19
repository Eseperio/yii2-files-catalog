<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\dictionaries\InodeTypes;
use paulzi\adjacencyList\AdjacencyListQueryTrait;
use yii\db\ActiveQuery;

/**
 * Class InodeQuery
 * @package eseperio\filescatalog\models
 * @method $this roots() gets the roots nodes
 */
class InodeQuery extends ActiveQuery
{

    use AdjacencyListQueryTrait;
    /**
     * Filters only root files
     * @return InodeQuery
     */
    public function onlyRoot()
    {
        return $this->roots()->limit(1);
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
