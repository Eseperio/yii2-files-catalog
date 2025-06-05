<?php

namespace tests\_fixtures;

use yii\test\ActiveFixture;

class InodeFixture extends ActiveFixture
{
    // Constantes para los IDs de los inodos fijos
    const ROOT_ID = 1;
    const DIR_ID = 2;
    const FILE_ID = 3;
    
    public $modelClass = 'eseperio\filescatalog\models\Inode';
    public $dataFile = "@tests/_fixtures/data/inode.php";


}
