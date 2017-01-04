<?php

namespace sergeymakinen\tests\log\helpers;

interface Tester
{
    public function test(\PHPUnit_Framework_TestCase $testCase, $actual);
}
