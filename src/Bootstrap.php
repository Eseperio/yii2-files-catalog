<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog;


use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    use ModuleAwareTrait;

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {

        if (!Yii::$app->hasModule('filex'))
            return;

        if ($app instanceof \yii\web\Application) {
            $module = self::getModule();
            $config = [
                'class' => 'yii\web\GroupUrlRule',
                'prefix' => $module->prefix,
                'routePrefix' => $module->prefix,
                'rules' => $module->urlRules,
            ];

            if ($module->prefix !== 'filex') {
                $config['routePrefix'] = 'filex';
            }


            $rule = Yii::createObject($config);
            $app->getUrlManager()->addRules([$rule], false);
        }
    }
}

