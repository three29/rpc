<?php

namespace Tests\Unit\View\Filter\Form;

use RPC\View\Filter\Form\Field;
use Tests\Unit\UnitTestCase;

class FieldTest extends UnitTestCase
{
    private $field;

    protected function setUp(): void
    {
        parent::setUp();
        $this->field = new Field();
    }

    public function testClassExists()
    {
        $this->assertTrue(class_exists(Field::class));
    }

    public function testCanInstantiate()
    {
        $this->assertInstanceOf(Field::class, $this->field);
    }

    public function testHasAttributeReturnsTrueWhenAttributeExists()
    {
        $html = '<input type="text" name="username" />';
        $this->assertTrue($this->field->hasAttribute($html, 'name'));
    }

    public function testHasAttributeReturnsFalseWhenAttributeMissing()
    {
        $html = '<input type="text" />';
        $this->assertFalse($this->field->hasAttribute($html, 'name'));
    }

    public function testGetAttributeReturnsQuotedValue()
    {
        $html = '<input type="text" name="username" />';
        $result = $this->field->getAttribute($html, 'name');

        $this->assertEquals("'username'", $result);
    }

    public function testGetAttributeReturnsEmptyStringForMissingAttribute()
    {
        $html = '<input type="text" />';
        $result = $this->field->getAttribute($html, 'name');

        $this->assertEquals("''", $result);
    }

    public function testRemoveAttributeRemovesAttribute()
    {
        $html = '<input type="text" name="username" id="user" />';
        $result = $this->field->removeAttribute($html, 'name');

        $this->assertStringNotContainsString('name="username"', $result);
        $this->assertStringContainsString('id="user"', $result);
    }

    public function testSetAttributeAddsAttribute()
    {
        $html = '<input type="text" />';
        $result = $this->field->setAttribute($html, 'name', 'username');

        $this->assertStringContainsString('name=', $result);
    }

    public function testGetAttributeHandlesEscapedQuotes()
    {
        $html = '<input type="text" name="user\'s_name" />';
        $result = $this->field->getAttribute($html, 'name');

        $this->assertStringContainsString("user\\'s_name", $result);
    }
}
