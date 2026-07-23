<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Textarea;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class TextareaTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Textarea::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Textarea();
        $this->assertInstanceOf(Textarea::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Textarea();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Textarea::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Textarea();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
