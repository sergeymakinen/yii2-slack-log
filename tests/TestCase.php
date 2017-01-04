<?php

namespace sergeymakinen\tests\log;

abstract class TestCase extends \sergeymakinen\tests\TestCase
{
    /**
     * Returns a test double for the specified class.
     * @param string $originalClassName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock($originalClassName)
    {
        $mock = $this
            ->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning();
        if (method_exists($mock, 'disallowMockingUnknownTypes')) {
            $mock->disallowMockingUnknownTypes();
        }
        return $mock->getMock();
    }
}
