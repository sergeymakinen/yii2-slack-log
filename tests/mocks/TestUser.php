<?php

namespace sergeymakinen\tests\log\mocks;

use yii\web\User;

class TestUser extends User
{
    /**
     * {@inheritdoc}
     */
    public function getIdentity($autoRenew = true)
    {
        return new TestIdentity();
    }
}
