<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;


use yii\data\ActiveDataProvider;

class InodeSearch extends \eseperio\filescatalog\models\Inode
{

    public function search($params = [])
    {
        $query = self::find();
        $this->load($params);
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        return $dataProvider;
    }
}
