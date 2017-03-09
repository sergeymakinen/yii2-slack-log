<?php

namespace sergeymakinen\yii\slacklog\tests\helpers;

use PHPUnit\Framework\TestCase;

interface Tester
{
    /**
     * @param TestCase|\PHPUnit_Framework_TestCase $testCase
     * @param mixed $actual
     */
    public function test($testCase, $actual);
}
