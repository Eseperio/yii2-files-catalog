<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use eseperio\filescatalog\services\InodeHelper;
use yii\data\ActiveDataProvider;

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

    public function search($params = [])
    {

        $this->load($params);

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
            'sort' => [
                'defaultOrder' => [
                    'name' => SORT_ASC
                ]
            ]
        ]);

        return $dataProvider;
    }
}
