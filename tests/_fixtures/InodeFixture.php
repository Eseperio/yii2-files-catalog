<?php

namespace tests\_fixtures;

use yii\test\ActiveFixture;

class InodeFixture extends ActiveFixture
{
    public $modelClass = 'eseperio\filescatalog\models\Inode';
    public $dataFile = "@tests/_fixtures/data/inode.php";
}
