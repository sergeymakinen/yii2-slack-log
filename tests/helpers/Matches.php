<?php

namespace sergeymakinen\yii\slacklog\tests\helpers;

class Matches implements Tester
{
    /**
     * @var string
     */
    private $_regEx;

    /**
     * @inheritDoc
     */
    public function __construct($regEx)
    {
        $this->_regEx = (string) $regEx;
    }

    /**
     * @inheritDoc
     */
    public function test($testCase, $actual)
    {
        $testCase->assertRegExp($this->_regEx, (string) $actual);
    }
}
