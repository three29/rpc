<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Checkbox;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class CheckboxTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Checkbox::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Checkbox();
        $this->assertInstanceOf(Checkbox::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Checkbox();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Checkbox::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Checkbox();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
