<?php

namespace sergeymakinen\yii\slacklog\tests\helpers;

class Contains implements Tester
{
    /**
     * @var array
     */
    private $_strings;

    /**
     * @inheritDoc
     */
    public function __construct($strings)
    {
        $this->_strings = (array) $strings;
    }

    /**
     * @inheritDoc
     */
    public function test($testCase, $actual)
    {
        foreach ($this->_strings as $string) {
            $testCase->assertContains((string) $string, (string) $actual);
        }
    }
}
