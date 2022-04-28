<?php

namespace eseperio\filescatalog\services;

use eseperio\filescatalog\models\Inode;

class ShareService
{
    /**
     * @var \eseperio\filescatalog\models\Inode
     */
    public $inode;

    /**
     * @param \eseperio\filescatalog\models\Inode $inode
     */
    public function __construct(Inode $inode)
    {
        $this->inode = $inode;
    }


    public function shareViaEmail($recipients)
    {
        $message= $this->prepareEmail();
        foreach ((array)$recipients as $recipient) {
        }
    }

    /**
     * @return void
     */
    private function prepareEmail()
    {
        \Yii::$app->get('filex');
        $mailer= \Yii::$app->get();
    }


}
