<?php

namespace sergeymakinen\tests\log\helpers;

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

    public function test(\PHPUnit_Framework_TestCase $testCase, $actual)
    {
        $testCase->assertRegExp($this->_regEx, (string) $actual);
    }
}
