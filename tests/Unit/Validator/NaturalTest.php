<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Natural;

class NaturalTest extends TestCase
{
    public function testValidatesPositiveInteger()
    {
        $validator = new Natural();
        $this->assertTrue($validator->validate(123));
    }

    public function testValidatesPositiveIntegerString()
    {
        $validator = new Natural();
        $this->assertTrue($validator->validate('123'));
    }

    public function testValidatesZero()
    {
        $validator = new Natural();
        $this->assertTrue($validator->validate(0));
    }

    public function testValidatesZeroString()
    {
        $validator = new Natural();
        $this->assertTrue($validator->validate('0'));
    }

    public function testRejectsNegativeInteger()
    {
        $validator = new Natural();
        $this->assertFalse($validator->validate(-123));
    }

    public function testRejectsNegativeIntegerString()
    {
        $validator = new Natural();
        $this->assertFalse($validator->validate('-123'));
    }

    public function testRejectsFloat()
    {
        $validator = new Natural();
        $this->assertFalse($validator->validate(12.34));
    }

    public function testRejectsFloatString()
    {
        $validator = new Natural();
        $this->assertFalse($validator->validate('12.34'));
    }

    public function testRejectsAlphabeticString()
    {
        $validator = new Natural();
        $this->assertFalse($validator->validate('abc'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Natural();
        $this->assertFalse($validator->validate(''));
    }
}
