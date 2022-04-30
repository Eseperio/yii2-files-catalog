<?php

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\ForbiddenHttpException;

/**
 * @property \eseperio\filescatalog\controllers\DefaultController $controller
 */
class RemoveShare extends Action
{
    use ModuleAwareTrait;

    /**
     * @return \yii\web\Response
     */
    public function run()
    {
        $request = Yii::$app->request;
        $inode = $this->controller->findModel($request->post('uuid'));
        if (empty($inode) || !AclHelper::canShare($inode)) {
            throw new ForbiddenHttpException();
        }

        $uuid = $request->post('user_id');
        $this->unshare($inode, $uuid);

        return $this->controller->redirect(['properties', 'uuid' => $inode->uuid]);
    }

    /**
     * Remove sharing for the specified option
     * @param \eseperio\filescatalog\models\Inode|\eseperio\filescatalog\models\Directory $inode
     * @param mixed $uuid
     * @return false|int
     * @throws \yii\db\StaleObjectException
     */
    protected function unshare($inode, mixed $uuid)
    {
        $shareObj = $inode->getShares()->where([
            'user_id' => $uuid
        ])->one();

        return $shareObj->delete();
    }


}
