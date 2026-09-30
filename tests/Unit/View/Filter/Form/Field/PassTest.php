<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Pass;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class PassTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Pass::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Pass();
        $this->assertInstanceOf(Pass::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Pass();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Pass::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Pass();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
