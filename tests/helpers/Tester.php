<?php

namespace sergeymakinen\tests\slacklog\helpers;

interface Tester
{
    public function test(\PHPUnit_Framework_TestCase $testCase, $actual);
}
