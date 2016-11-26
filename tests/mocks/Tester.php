<?php

namespace sergeymakinen\tests\mocks;

interface Tester
{
    public function test(\PHPUnit_Framework_TestCase $testCase, $actual);
}
