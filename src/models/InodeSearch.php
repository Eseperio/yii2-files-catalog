<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\data\ActiveDataProvider;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\services\InodeHelper;

class InodeSearch extends \eseperio\filescatalog\models\Inode
{

    /**
     * Mode chidren performs search only from children
     */
    const MODE_CHILDREN = 1;
    /**
     * Mode descendants performs search across all descendants, no matter depth.
     */
    const MODE_DESCENDANTS = 2;


    public function rules()
    {
        return [
            ['uuid', 'required'],
//        @todo: Disabled until a better way of handling searches is found
//            ['extension', 'string'],
            ['name', 'string', 'min' => 3],
            ['uuid', 'string', 'min' => 36, 'max' => 36]
        ];
    }

    /**
     * @param array $params
     * @param null $model Override model to be used while searching
     * @return ActiveDataProvider
     * @throws \eseperio\filescatalog\exceptions\FilexAccessDeniedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function search($params = [], $model = null, $mode = 1)
    {

        $query = $this->getSearchQuery($params, $model, $mode);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'uuid',
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC
                ]
            ]
        ]);

        return $dataProvider;
    }

    /**
     * @param array $params
     * @param $model
     * @param mixed $mode
     * @return InodeQuery
     * @throws \eseperio\filescatalog\exceptions\FilexAccessDeniedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function getSearchQuery(array $params, $model = null, mixed $mode = self::MODE_CHILDREN): InodeQuery
    {
        $this->load($params);

        if (empty($model))
            $model = InodeHelper::getModel($this->uuid);

        if ($mode === self::MODE_DESCENDANTS) {
            $query = $model->getDescendants()
                ->ofType([InodeTypes::TYPE_FILE, InodeTypes::TYPE_DIR]);
        } else {
            $query = $model->getChildren()
                ->withSymlinksReferences();
        }


        $query->excludeVersions()
            ->onlyReadable();

        if($this->module->enableUserSharing){
            $query->withSharesActive();
        }

        $query->andFilterWhere([InodeQuery::prefix('extension') => $this->extension]);

        if (!empty($this->name)) {
            $keywords = explode(" ", $this->name);
            foreach ($keywords as $keyword) {
                $query->andWhere(['like', InodeQuery::prefix('name'), trim($keyword)]);
            }
        }

        if (self::getModule()->groupFilesByExt)
            $query->orderByExtension();
        return $query;
    }
}
