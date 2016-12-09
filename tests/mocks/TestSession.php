<?php

namespace sergeymakinen\tests\log\mocks;

use yii\web\Session;

class TestSession extends Session
{
    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'session_id';
    }
}
