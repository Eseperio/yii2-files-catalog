<?php

namespace app\models;

use yii\web\IdentityInterface;

class UserIdentity implements IdentityInterface
{
    const USER_A = 1;
    const USER_B = 2;
    const USER_C = 3;
    const USER_D = 4;
    const USER_E = 5;

    /**
     * PASSWORD: password
     * @var array[]
     */
    public static $users = [
        self::USER_A => [
            'id' => self::USER_A,
            'username' => 'user1',
            'auth_key' => 'test100key',
            'access_token' => 'token-1'
        ],
        self::USER_B => [
            'id' => self::USER_B,
            'username' => 'user2',
            'auth_key' => 'test200key',
            'access_token' => 'token-2'
        ],
        self::USER_C => [
            'id' => self::USER_C,
            'username' => 'user3',
            'auth_key' => 'test300key',
            'access_token' => 'token-3'
        ],
        self::USER_D => [
            'id' => self::USER_D,
            'username' => 'user4',
            'auth_key' => 'test400key',
            'access_token' => 'token-4'
        ],
        self::USER_E => [
            'id' => self::USER_E,
            'username' => 'user5',
            'auth_key' => 'test500key',
            'access_token' => 'token-5'
        ],
    ];


    public $id;
    public $username;
    public $auth_key;
    public $access_token;

    public static function findIdentity($id)
    {
        if (isset(self::$users[$id])) {
            $identity = new static();
            $identity->id = self::$users[$id]['id'];
            $identity->username = self::$users[$id]['username'];
            $identity->auth_key = self::$users[$id]['auth_key'];
            $identity->access_token = self::$users[$id]['access_token'];
            return $identity;
        }
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['access_token'] === $token) {
                $identity = new static();
                $identity->id = $user['id'];
                $identity->username = $user['username'];
                $identity->auth_key = $user['auth_key'];
                $identity->access_token = $user['access_token'];
                return $identity;
            }
        }
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
}
