<?php

namespace app\controllers;

use Da\User\Filter\AccessRuleFilter;
use eseperio\filescatalog\actions\NewLinkAction;
use yii\filters\AccessControl;
use yii\web\Controller;

class SiteController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }
}
