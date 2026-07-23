<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\IsNumeric;

class IsNumericTest extends TestCase
{
    public function testValidatesInteger()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate(123));
    }

    public function testValidatesIntegerString()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate('123'));
    }

    public function testValidatesFloat()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate(12.34));
    }

    public function testValidatesFloatString()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate('12.34'));
    }

    public function testValidatesNegativeNumber()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate('-123'));
    }

    public function testValidatesZero()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate(0));
    }

    public function testValidatesScientificNotation()
    {
        $validator = new IsNumeric();
        $this->assertTrue($validator->validate('1.23e4'));
    }

    public function testRejectsAlphabeticString()
    {
        $validator = new IsNumeric();
        $this->assertFalse($validator->validate('abc'));
    }

    public function testRejectsAlphanumericString()
    {
        $validator = new IsNumeric();
        $this->assertFalse($validator->validate('abc123'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new IsNumeric();
        $this->assertFalse($validator->validate(''));
    }
}
