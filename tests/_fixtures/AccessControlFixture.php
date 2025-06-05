<?php

namespace tests\_fixtures;

use eseperio\filescatalog\models\AccessControl;
use yii\test\ActiveFixture;

class AccessControlFixture extends ActiveFixture
{
    public $modelClass = AccessControl::class;

    public $depends = [
        'tests\_fixtures\InodeFixture',
    ];


}
