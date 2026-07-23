<?php

namespace Tests\Unit\View\Filter\Datagrid;

use RPC\View\Filter\Datagrid\Sort;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class SortTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Sort::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Sort();
        $this->assertInstanceOf(Sort::class, $filter);
    }

    public function testExtendsBaseFilter()
    {
        $filter = new Sort();
        $this->assertInstanceOf(Filter::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Sort::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Sort();
        $result = $filter->filter('test content');

        $this->assertIsString($result);
    }
}
