<?php

namespace Tests\Unit\View\Filter\Datagrid;

use RPC\View\Filter\Datagrid\Pagination;
use RPC\View\Filter;
use Tests\Unit\UnitTestCase;

class PaginationTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Pagination::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Pagination();
        $this->assertInstanceOf(Pagination::class, $filter);
    }

    public function testExtendsBaseFilter()
    {
        $filter = new Pagination();
        $this->assertInstanceOf(Filter::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Pagination::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Pagination();
        $result = $filter->filter('test content');

        $this->assertIsString($result);
    }
}
