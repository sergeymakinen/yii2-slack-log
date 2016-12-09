<?php

namespace sergeymakinen\tests\log\mocks;

class Matches implements Tester
{
    /**
     * @var string
     */
    private $_regEx;

    /**
     * {@inheritdoc}
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
