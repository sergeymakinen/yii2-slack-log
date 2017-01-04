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
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
    }
}
