<?php

namespace sergeymakinen\tests\log\mocks;

use yii\base\InvalidCallException;
use yii\web\IdentityInterface;

class TestIdentity implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        throw new InvalidCallException('Mocked');
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new InvalidCallException('Mocked');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'userId';
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        throw new InvalidCallException('Mocked');
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        throw new InvalidCallException('Mocked');
    }
}
