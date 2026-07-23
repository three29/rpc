<?php

namespace Tests\Unit\View\Filter\Form\Field;

use RPC\View\Filter\Form\Field\Hidden;
use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class HiddenTest extends UnitTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(Hidden::class));
    }

    public function testCanInstantiate()
    {
        $filter = new Hidden();
        $this->assertInstanceOf(Hidden::class, $filter);
    }

    public function testExtendsFieldBase()
    {
        $filter = new Hidden();
        $this->assertInstanceOf(Field::class, $filter);
    }

    public function testHasFilterMethod()
    {
        $this->assertTrue(method_exists(Hidden::class, 'filter'));
    }

    public function testFilterReturnsString()
    {
        $filter = new Hidden();
        $result = $filter->filter('<div>test</div>');

        $this->assertIsString($result);
    }
}
