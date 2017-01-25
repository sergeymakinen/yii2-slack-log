<?php

namespace sergeymakinen\yii\slacklog\tests\helpers;

interface Tester
{
    public function test(\PHPUnit_Framework_TestCase $testCase, $actual);
}
