<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Text;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class TextTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Text::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Text();
        $this->assertInstanceOf(Text::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Text();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Text::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Text();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
