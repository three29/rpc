<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Radio;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class RadioTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Radio::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Radio();
        $this->assertInstanceOf(Radio::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Radio();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Radio::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Radio();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
