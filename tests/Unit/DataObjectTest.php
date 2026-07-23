<?php

namespace Tests\Unit;

use RPC\DataObject;

class DataObjectTest extends UnitTestCase
{
    public function testDataObjectCanBeInstantiated()
    {
        $obj = new DataObject();

        $this->assertInstanceOf(DataObject::class, $obj);
    }

    public function testDataObjectCanHavePropertiesSet()
    {
        $obj = new DataObject();
        $obj->name = 'Test';
        $obj->value = 123;

        $this->assertEquals('Test', $obj->name);
        $this->assertEquals(123, $obj->value);
    }

    public function testDataObjectCanHaveMultipleProperties()
    {
        $obj = new DataObject();
        $obj->id = 1;
        $obj->name = 'John Doe';
        $obj->email = 'john@example.com';
        $obj->active = true;

        $this->assertEquals(1, $obj->id);
        $this->assertEquals('John Doe', $obj->name);
        $this->assertEquals('john@example.com', $obj->email);
        $this->assertTrue($obj->active);
    }

    public function testDataObjectPropertyCanBeUnset()
    {
        $obj = new DataObject();
        $obj->temp = 'temporary';

        $this->assertTrue(isset($obj->temp));

        unset($obj->temp);

        $this->assertFalse(isset($obj->temp));
    }

    public function testDataObjectIsNotStdClass()
    {
        $obj = new DataObject();

        $this->assertNotInstanceOf(\stdClass::class, $obj);
    }

    public function testDataObjectCanHaveNestedObjects()
    {
        $obj = new DataObject();
        $obj->nested = new DataObject();
        $obj->nested->value = 'nested';

        $this->assertInstanceOf(DataObject::class, $obj->nested);
        $this->assertEquals('nested', $obj->nested->value);
    }

    public function testDataObjectCanHaveArrayProperty()
    {
        $obj = new DataObject();
        $obj->items = [1, 2, 3];

        $this->assertEquals([1, 2, 3], $obj->items);
    }

    public function testDataObjectClassExists()
    {
        $this->assertTrue(class_exists(DataObject::class));
    }
}
