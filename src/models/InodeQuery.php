<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use paulzi\adjacencyList\AdjacencyListQueryTrait;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Class InodeQuery
 * @package eseperio\filescatalog\models
 * @mixin   AdjacencyListQueryTrait
 */
class InodeQuery extends ActiveQuery
{
    use ModuleAwareTrait;
    use AdjacencyListQueryTrait;


    const WRITE_MASKS = [7, 6, 3, 2];
    const DELETE_MASKS = [1, 3, 5, 7];
    const READABLE_MASKS = [7, 6, 5, 4];

    private $formatLimited = false;

    /**
     * Filters only root files
     * @return InodeQuery
     */
    public function onlyRoot()
    {
        return $this->roots()->limit(1);
    }

    /**
     * @return InodeQuery
     */
    public function excludeVersions()
    {
        return $this->andWhere(['!=', self::prefix('type'), InodeTypes::TYPE_VERSION]);
    }

    /**
     * @param $column
     * @return string
     */
    public static function prefix($column, $prefix = null)
    {
        if (empty($prefix))
            $prefix = Inode::tableName() . ".";

        return $prefix . $column;
    }

    /**
     * Join with symlinks
     * @return $this
     */
    public function withSymlinksReferences()
    {

        $selectColumns = [
            Inode::tableName() . ".*",
        ];
        foreach (['name', 'type', 'extension'] as $columnName) {
            $selectColumns[] = self::prefix($columnName, 'symlink.') . " " . self::prefix($columnName, 'symlink_');
        }
        $this->select($selectColumns);

        $this->join('LEFT OUTER JOIN', ['symlink' => Inode::tableName()], Inode::tableName()
            . '.uuid=symlink.uuid AND ' . self::prefix('type', 'symlink.') . '!=' . InodeTypes::TYPE_SYMLINK
            . ' AND ' . self::prefix('type', 'symlink.') . '!=' . InodeTypes::TYPE_SYMLINK);

        return $this;
    }

    public function byType(array $types)
    {
        return $this->andWhere([self::prefix('type') => $types]);
    }

    /**
     * Filter results to only those which user have permission to read
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function onlyReadable()
    {
        return $this->onlyAllowed(self::READABLE_MASKS);
    }

    /**
     * Joins results with acl table, so it can be filtered by crud_mask values
     * @param $crudMaskValues array|integer of masks to filter with
     * @return InodeQuery
     * @throws \yii\base\InvalidConfigException
     * @internal
     */
    private function onlyAllowed($crudMaskValues): InodeQuery
    {
        // we use false comparison to prevent null values disabling ACL
        if (!$this->module->isAdmin() && $this->module->enableACL !== false) {
            $authManager = Yii::$app->authManager;
            $userId = $this->module->getUserId();

            $userRoles = ArrayHelper::getColumn($authManager->getRolesByUser($userId), 'name', false);
            $allPermissions = ArrayHelper::getColumn($authManager->getPermissionsByUser($userId), 'name', false);
            $allPermissions = array_merge($userRoles, $allPermissions);
            $allPermissions[] = AccessControl::WILDCARD_ROLE;

            $this->joinWith('accessControlList acl');

            $condition = ['or',
                ['acl.role' => $allPermissions],
                ['acl.user_id' => $userId],
            ];

            if (!$this->module->getUser()->getIsGuest())
                $condition[] = ['acl.role' => AccessControl::LOGGED_IN_USERS];

            $this->andWhere($condition);
            $this->andWhere(['acl.crud_mask' => $crudMaskValues]);
            $this->groupBy(self::prefix('id'));

        }

        return $this;
    }

    /**
     * Filter results to only those which user have permission to write
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function onlyWriteable()
    {
        return $this->onlyAllowed(self::WRITE_MASKS);
    }

    /**
     * Filter results to only those which user have permission to delete
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function onlyDeletable()
    {
        return $this->onlyAllowed(self::DELETE_MASKS);
    }

    /**
     * @param $uuid
     * @return InodeQuery
     */
    public function uuid($uuid)
    {
        return $this->andWhere([self::prefix('uuid') => $uuid]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderByExtension($order = SORT_DESC)
    {
        return $this->addOrderBy([
            self::prefix('extension') => $order
        ]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderByType($order = SORT_DESC)
    {
        return $this->addOrderBy([
            self::prefix('type') => $order,
        ]);
    }

    /**
     * @param int $order
     * @return InodeQuery
     */
    public function orderAZ($order = SORT_ASC)
    {
        return $this->addOrderBy([
            self::prefix('name') => $order
        ]);
    }

    /**
     * @return InodeQuery
     */
    public function onlySymlinks()
    {
        $this->limitedByFormat();

        return $this->andWhere([
            self::prefix('type') => InodeTypes::TYPE_SYMLINK
        ]);
    }

    public function limitedByFormat()
    {
        if ($this->formatLimited)
            throw new InvalidArgumentException(__CLASS__ . ' has two type filters that collides');
        $this->formatLimited = true;
    }

    /**
     * @param string $name
     * @param bool $like
     * @return InodeQuery
     */
    public function byName(string $name, $like = false)
    {
        if ($like)
            return $this->andWhere(['like', 'name', $name]);

        return $this->andWhere(['name' => $name]);
    }


    /**
     * @return InodeQuery
     */
    public function onlyFiles()
    {
        $this->limitedByFormat();

        return $this->andWhere([
            self::prefix('type') => InodeTypes::TYPE_FILE
        ]);
    }

    /**
     * @return InodeQuery
     */
    public function onlyDirs()
    {
        $this->limitedByFormat();

        return $this->andWhere([
            self::prefix('type') => InodeTypes::TYPE_DIR
        ]);
    }

    /**
     * Filters query by the types supplied
     * @return InodeQuery
     */
    public function ofType($types)
    {
        $this->limitedByFormat();

        return $this->andWhere([
            self::prefix('type') => $types
        ]);
    }

    /**
     * Filters results to those shared with current user
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function sharedWithMe()
    {
        return $this->sharedWith($this->module->getUserId());
    }

    /**
     * Filters the results to all those shared with certain user
     * @return $this
     * @throws \yii\base\InvalidConfigException
     */
    public function sharedWith($userId)
    {
        $SharesTable = InodeShare::tableName();
        $this
            ->onlyReadable()
            ->joinWith("shares")
            ->andWhere([
                'AND',
                ["{$SharesTable}.user_id" => $userId],
                [
                    'OR',
                    ['>', "{$SharesTable}.expires_at", time()],
                    ["{$SharesTable}.expires_at" => null],
                ],
            ]);
        return $this;
    }

    /**
     * Joins shares table and fill a virtual property called shared with the amount of shares
     * but only those active
     * @return $this
     */
    public function withSharesActive()
    {
        return $this->withShares(true);
    }

    /**
     * Joins shares table and fill a virtual property called shared with the amount of shares
     * @return \eseperio\filescatalog\models\InodeQuery
     */
    public function withShares($onlyActive = false)
    {
        if (!$this->module->enableUserSharing) {
            return $this;
        }
        $sharesTable = InodeShare::tableName();
        $relation = $onlyActive ? "sharesActive" : "shares";
        $this->joinWith($relation, false);
        $this->groupBy(self::prefix('id'));
        $this->addSelect([self::prefix('*'), new Expression("count({$sharesTable}.user_id) as shared")]);
        return $this;
    }
}
