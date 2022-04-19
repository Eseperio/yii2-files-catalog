<?php

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\services\InodeHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\base\UserException;
use yii\web\NotFoundHttpException;

/**
 * @property $module FilesCatalogModule
 */
class ShareViaEmail extends Action
{
    use ModuleAwareTrait;

    /**
     * @var DynamicModel buffer for dynamic model
     */
    private $formModelInstance;
    /**
     * @var Inode;
     */
    private $inode;

    /**
     * @return \yii\base\DynamicModel
     */
    public function getFormModel()
    {
        if (empty($this->formModelInstance)) {
            $formModel = new DynamicModel(['recipient', 'message']);
            $formModel->addRule(['recipient'], 'email');
            $formModel->addRule(['message'], 'string', ['max' => 256]);
            $this->formModelInstance = $formModel;
        }

        return $this->formModelInstance;
    }

    public function run()
    {
        $formModel = $this->getFormModel();
        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            $this->share();

        }


        $model = $this->getModel();
        if ($model->type !== InodeTypes::TYPE_FILE && !$model->isRoot()) {
            throw new UserException(Yii::t('xenon', 'Cannot share directories or symlinks via email'));
        }


    }

    /**
     * @return array|\eseperio\filescatalog\models\Directory|\eseperio\filescatalog\models\Inode|\eseperio\filescatalog\models\Symlink
     * @throws \eseperio\filescatalog\exceptions\FilexAccessDeniedException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     */
    protected function getModel()
    {
        if (!empty($this->inode)) {
            return $this->inode;
        }

        $this->inode = InodeHelper::getModel(Yii::$app->request->get('uuid', false));

        if (empty($this->inode)) {
            throw new NotFoundHttpException();
        }
        return $this->inode;
    }

    /**
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    private function share()
    {
        /* @var $mailer \yii\mail\MailerInterface */
        $mailer = Yii::$app->get($this->module->mailer);

        $mailer->compose('email', [
            'username' => Yii::$app->user->identity->getFullname(),
            'filename' => $this->inode->getPublicName()
        ])
            ->attachContent($this->inode->getFile());


    }
}
