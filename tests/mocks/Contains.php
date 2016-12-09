<?php

namespace sergeymakinen\tests\log\mocks;

class Contains implements Tester
{
    /**
     * @var array
     */
    private $_strings;

    /**
     * {@inheritdoc}
     */
    public function __construct($strings)
    {
        $this->_strings = (array) $strings;
    }

    public function test(\PHPUnit_Framework_TestCase $testCase, $actual)
    {
        foreach ($this->_strings as $string) {
            $testCase->assertContains((string) $string, (string) $actual);
        }
    }
}
