<?php

namespace Tests\Unit\Validator;

use RPC\Validator\Integer;
use RPC\Validator;
use Tests\Unit\UnitTestCase;

class IntegerTest extends UnitTestCase
{
    private Integer $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Integer();
    }

    public function testValidatorExtendsBaseValidator()
    {
        $this->assertInstanceOf(Validator::class, $this->validator);
    }

    public function testValidateReturnsTrueForInteger()
    {
        $this->assertTrue($this->validator->validate(42));
        $this->assertTrue($this->validator->validate(0));
        $this->assertTrue($this->validator->validate(-100));
    }

    public function testValidateReturnsFalseForFloat()
    {
        $this->assertFalse($this->validator->validate(3.14));
        $this->assertFalse($this->validator->validate(0.5));
        $this->assertFalse($this->validator->validate(-2.7));
    }

    public function testValidateReturnsFalseForString()
    {
        $this->assertFalse($this->validator->validate('42'));
        $this->assertFalse($this->validator->validate('123'));
        $this->assertFalse($this->validator->validate('not a number'));
    }

    public function testValidateReturnsFalseForNumericString()
    {
        // Even though the string is numeric, it's not an actual integer type
        $this->assertFalse($this->validator->validate('123'));
        $this->assertFalse($this->validator->validate('0'));
    }

    public function testValidateReturnsFalseForBoolean()
    {
        $this->assertFalse($this->validator->validate(true));
        $this->assertFalse($this->validator->validate(false));
    }

    public function testValidateReturnsFalseForNull()
    {
        $this->assertFalse($this->validator->validate(null));
    }

    public function testValidateReturnsFalseForArray()
    {
        $this->assertFalse($this->validator->validate([]));
        $this->assertFalse($this->validator->validate([1, 2, 3]));
    }

    public function testValidateReturnsFalseForObject()
    {
        // Suppress warning about object to int conversion
        $this->assertFalse(@$this->validator->validate(new \stdClass()));
    }

    public function testValidateWithLargeInteger()
    {
        $this->assertTrue($this->validator->validate(PHP_INT_MAX));
        $this->assertTrue($this->validator->validate(PHP_INT_MIN));
    }

    public function testValidateWithZero()
    {
        $this->assertTrue($this->validator->validate(0));
    }
}
