<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Phone;

class PhoneTest extends TestCase
{
    public function testValidates10DigitPhone()
    {
        $validator = new Phone();
        $this->assertTrue($validator->validate('1234567890'));
    }

    public function testValidatesPhoneWithDashes()
    {
        $validator = new Phone();
        $this->assertTrue($validator->validate('123-456-7890'));
    }

    public function testValidatesPhoneWithParentheses()
    {
        $validator = new Phone();
        $this->assertTrue($validator->validate('(123) 456-7890'));
    }

    public function testValidatesPhoneWithDots()
    {
        $validator = new Phone();
        $this->assertTrue($validator->validate('123.456.7890'));
    }

    public function testValidatesPhoneWithSpaces()
    {
        $validator = new Phone();
        $this->assertTrue($validator->validate('123 456 7890'));
    }

    public function testRejectsTooFewDigits()
    {
        $validator = new Phone();
        $this->assertFalse($validator->validate('123456789'));
    }

    public function testRejectsTooManyDigits()
    {
        $validator = new Phone();
        $this->assertFalse($validator->validate('12345678901'));
    }

    public function testRejectsAlphabeticCharacters()
    {
        $validator = new Phone();
        $this->assertFalse($validator->validate('abc-def-ghij'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Phone();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsAlphanumericMix()
    {
        $validator = new Phone();
        $this->assertFalse($validator->validate('123-abc-7890'));
    }
}
