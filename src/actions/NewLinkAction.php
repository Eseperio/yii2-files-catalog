<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\assets\FileTypeIconsAsset;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Directory;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\services\InodeHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;


class NewLinkAction extends Action
{
    use ModuleAwareTrait;

    public function run()
    {

        FileTypeIconsAsset::register($this->controller->view);

        $uuid = Yii::$app->request->getQueryParam('uuid', false);
        $parent = Directory::find()->uuid($uuid)->one();
        $remoteUuid = Yii::$app->request->post('ruuid', false);
        $remote = Inode::find()->byType([InodeTypes::TYPE_FILE, InodeTypes::TYPE_DIR])->uuid($remoteUuid)->one();

        if (empty($parent))
            throw new NotFoundHttpException('Page not found');

        if (!AclHelper::canWrite($parent))
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'You can not create items in this folder'));


        $query = Inode::find()
            ->byType([InodeTypes::TYPE_FILE, InodeTypes::TYPE_DIR])
            ->onlyReadable()
            ->limit(20);

        if (Yii::$app->request->isPost && !empty($parent) && !empty($remote)) {
            if (InodeHelper::linkToInode($remote, $parent)) {
                return $this->controller->goBack();
            }
        }
        $model = new DynamicModel([
            'query',
        ]);
        $model->addRule('query', RequiredValidator::class);
        $model->addRule('query', StringValidator::class, ['min' => 3]);
        if ($model->load(Yii::$app->request->queryParams) && $model->validate()) {
            if (mb_strlen($model->query) == 36) {
                $query->uuid($model->query);
            } else {
                $query->byName($model->query, true);
            }
        } else {
            $query->where('0=1');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $trans = Yii::$app->db->beginTransaction();

        try {
            $trans->commit();
//            return $this->controller->redirect(['index', 'uuid' => $parent->uuid]);
        } catch (\Throwable $e) {
            $trans->rollBack();
        }


        return $this->controller->render('new-link', [
            'model' => $model,
            'parent' => $parent,
            'dataProvider' => $dataProvider
        ]);

    }
}
