<?php

namespace eseperio\filescatalog\validators;

use eseperio\filescatalog\models\Inode;
use yii;
use yii\validators\Validator;


/**
 * Class UniqueFilenameOnFolderValidator
 * @package eseperio\filescatalog\validators
 */
class UniqueFilenameOnFolderValidator extends Validator
{
    /**
     * @var Inode
     */
    public $inode;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (empty($this->inode))
            throw new yii\base\InvalidConfigException(Yii::t('filescatalog', 'Inode is required'));
    }

    /**
     * @param yii\base\Model $model
     * @param string $attribute
     */
    public function validateAttribute($model, $attribute)
    {
        $parent = $this->inode->getParent()->one();
        $siblingsNames = $parent->getChildren()
            ->onlyFiles()
            ->andWhere(['not', ['id' => $this->inode->id]])
            ->asArray()
            ->select('name')
            ->column();

        if (in_array($model->name, $siblingsNames))
            $model->addError('name', Yii::t('filescatalog', "File name already exists in this folder",
                [
                    'name' => $model->name,
                ]));

        return;

    }
}
