<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use yii\helpers\Html;
use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeSearch;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use http\Url;
use Yii;
use yii\base\Action;
use yii\web\Controller;

class ViewAction extends Action
{
    use ModuleAwareTrait;

    /**
     * @var Inode main model being viewed
     */
    public $model;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    /**
     * @param $model Inode
     * @param $offset int considering current item as 0. Previous: -1, next +1
     * @return array|Inode|\yii\db\ActiveRecord|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function getInodeByOffset($parentId, $offset)
    {

        $sortAttribute = Yii::$app->request->get($this->module->sortParam);
        if ($offset < 0) {
            return null;
        }
        $query = Inode::find()
            ->where(['parent_id' => $parentId])
            ->onlyReadable()
            ->excludeVersions()
            ->offset($offset)
            ->limit(1);

        if (!empty($sortAttribute)) {
            $order = SORT_ASC;
            if (strncmp($sortAttribute, '-', 1) === 0) {
                $order = SORT_DESC;
                $sortAttribute = substr($sortAttribute, 1);
            }
            $query->orderBy([$sortAttribute => $order]);
        } else {
            $query->orderBy(['name' => SORT_ASC]);
        }

        return $query->one();

    }


    public function run()
    {
        $this->model = $this->controller->findModel(Yii::$app->request->get('uuid'), Inode::class);
        if ($this->model->type == InodeTypes::TYPE_DIR)
            return $this->controller->redirect(['index', 'uuid' => $this->model->uuid]);
        $versions = $this->model->versions;

        if (!empty($versions) && is_array($versions) && !Yii::$app->request->get('original', false))
            $this->model = end($versions);

        /**
         * $tag
         *  null: No tag available. Display download button
         *  false: Files does not exist. Display error message.
         */
        $tag = null;

        if (!$this->model->fileExists()) {
            $tag = false;
        }

        $allowedMimes = $this->module->browserInlineMimeTypes;
        if (is_null($tag) && array_key_exists($this->model->mime, $allowedMimes)
            && $this->model->filesize < $this->module->maxInlineFileSize) {
            $tagName = $allowedMimes[$this->model->mime];
            $tag = $this->getTag($tagName, $this->model->getContentAsBase64(), $this->model);
        }


        return $this->controller->render('view', [
            'model' => $this->model,
            'tag' => $tag,
            'nextLink' => $this->getNearItemLink(SORT_ASC),
            'prevLink' => $this->getNearItemLink(SORT_DESC),
            'checkFilesIntegrity' => $this->module->checkFilesIntegrity,
        ]);

    }

    /**
     * @param $tagName
     * @param string $base64data
     * @param $model
     * @return string
     */
    protected function getTag($tagName, string $base64data, $model): string
    {
        switch ($tagName) {
            case 'video':
            case 'audio':
                $tag = $this->getAudioVideoTag($tagName, $base64data, $model);
                break;
            case 'img':
                $tag = $this->getImgTag($base64data);
                break;
            case 'iframe':
                $tag = $this->getIframeTag($base64data);
                break;
        }

        return $tag;
    }

    /**
     * @param $tagName
     * @param string $base64data
     * @param $model
     * @return string
     */
    protected function getAudioVideoTag($tagName, string $base64data, $model): string
    {
        $tag = Html::tag($tagName, Html::tag('source', '', [
            'src' => $base64data,
            'type' => $model->mime
        ]), ['controls' => 1]);

        return $tag;
    }

    /**
     * @param string $base64data
     * @return string
     */
    protected function getImgTag(string $base64data): string
    {
        $tag = Html::img($base64data, [
            'class' => 'img-responsive'
        ]);

        return $tag;
    }

    /**
     * @param string $base64data
     * @return string
     */
    protected function getIframeTag(string $base64data): string
    {
        $tag = Html::tag('iframe', '', [
            'src' => $base64data,
            'style' => [
                'width' => '100%',
                'min-height' => '70vh'
            ]
        ]);

        return $tag;
    }

    /**
     * Search for the nearest item based on the offset
     * @param int $direction for searching. Use sort constants SORT_ASC and SORT_DESC
     * @return array|false
     * @throws \yii\base\InvalidConfigException
     */
    protected function getNearItemLink($direction = SORT_ASC)
    {
        $parentId = $this->model->parent_id;
        $offset = Yii::$app->request->get($this->module->offsetParam);
        $nearModel = $this->getInodeByOffset($parentId, $direction == SORT_DESC ? $offset - 1 : $offset + 1);

        if ($direction === SORT_ASC) {
            $offset++;
        } else {
            $offset--;
        }

        if ($offset !== false && !is_null($offset)) {
            if (!empty($nearModel)) {
                while ($nearModel->type === InodeTypes::TYPE_DIR || $nearModel->type === InodeTypes::TYPE_SYMLINK) { // Skip folders
                    if ($direction === SORT_ASC) {
                        $offset++;
                    } else {
                        $offset--;
                    }
                    $nearModel = $this->getInodeByOffset($parentId, $offset);
                    if (empty($nearModel)) {
                        return false;
                    }
                }

                return [
                    'view',
                    'uuid' => $nearModel->uuid,
                    $this->module->offsetParam => $offset,
                    $this->module->sortParam => Yii::$app->request->get($this->module->sortParam)
                ];
            }


        } else {
            return false;
        }

    }

}
