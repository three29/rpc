<?php

namespace Tests\Unit\View\Filter;

use RPC\View\Filter\Datagrid;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class DatagridTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Datagrid::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Datagrid();
        $this->assertInstanceOf(Datagrid::class, $filter);
    }

    public function testExtendsBaseFilter()
    {
        $filter = new Datagrid();
        $this->assertInstanceOf(Filter::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Datagrid::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Datagrid();
        $result = $filter->filter('test content');

        $this->assertIsString($result);
    }
}
