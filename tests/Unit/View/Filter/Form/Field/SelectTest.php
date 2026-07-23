<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Select;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class SelectTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Select::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Select();
        $this->assertInstanceOf(Select::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Select();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Select::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Select();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
