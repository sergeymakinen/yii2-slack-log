<?php

namespace sergeymakinen\tests\log\mocks;

interface Tester
{
    public function test(\PHPUnit_Framework_TestCase $testCase, $actual);
}
