<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * Class Bulk is the base class for bulk actions. Contains common functions
 * @package eseperio\filescatalog\actions
 */
abstract class Bulk extends Action
{
    use ModuleAwareTrait;

    /**
     * @return array|Inode[]|\yii\db\ActiveRecord[]
     * @throws ForbiddenHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function getModels()
    {
        $uuids = Yii::$app->request->post('uuids');
        if (!is_array($uuids))
            throw new ForbiddenHttpException();

        return File::find()->where(['uuid' => $uuids])->onlyDeletable()->all();
    }

    abstract public function run();

    /**
     * @param array $models
     * @return bool
     */
    protected function checkSecureHash(array $models): bool
    {
        $hash = $this->getSecureHash($models);

        return Yii::$app->request->post($this->module->secureHashParamName) == $hash
            && Yii::$app->request->post('confirm_text') === mb_substr($hash, 0, 5);
    }

    /**
     * @param $collection \eseperio\filescatalog\models\base\Inode[]
     * @return string
     */
    protected function getSecureHash($collection)
    {
        $ids = ArrayHelper::getColumn($collection, 'id');

        return hash('SHA3-256', join($ids) . $this->module->salt);
    }
}
