<?php

namespace sergeymakinen\tests\slacklog\stubs;

use yii\web\User;

class TestUser extends User
{
    /**
     * @inheritDoc
     */
    public function getIdentity($autoRenew = true)
    {
        return new TestIdentity();
    }
}
