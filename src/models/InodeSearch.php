<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\data\ActiveDataProvider;
use eseperio\filescatalog\services\InodeHelper;

class InodeSearch extends \eseperio\filescatalog\models\Inode
{

    public function rules()
    {
        return [
            ['uuid', 'required'],
            ['name', 'string', 'min' => 3, 'max' => 20],
            ['uuid', 'string', 'min' => 36, 'max' => 36]
        ];
    }

    /**
     * @param array $params
     * @param null $model Override model to be used when searching
     * @return ActiveDataProvider
     * @throws \eseperio\filescatalog\exceptions\FilexAccessDeniedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    public function search($params = [], $model = null)
    {

        $this->load($params);

        if (empty($model))
            $model = InodeHelper::getModel($this->uuid);

        $query = $model->getChildren();


        $query->with(['accessControlList'])
            ->excludeVersions()
            ->withSymlinksReferences()
            ->onlyReadable();
        $query->andFilterWhere(['like', InodeQuery::prefix('name'), $this->name]);

        if (self::getModule()->groupFilesByExt)
            $query->orderByExtension();

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
}
