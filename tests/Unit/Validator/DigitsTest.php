<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Digits;

class DigitsTest extends TestCase
{
    public function testValidatesDigitString()
    {
        $validator = new Digits();
        $this->assertTrue($validator->validate('12345'));
    }

    public function testValidatesSingleDigit()
    {
        $validator = new Digits();
        $this->assertTrue($validator->validate('0'));
    }

    public function testValidatesZero()
    {
        $validator = new Digits();
        $this->assertTrue($validator->validate('0'));
    }

    public function testRejectsAlphabeticString()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate('abc'));
    }

    public function testRejectsAlphanumericString()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate('abc123'));
    }

    public function testRejectsNegativeNumber()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate('-123'));
    }

    public function testRejectsFloatString()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate('12.34'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsWhitespace()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate('123 456'));
    }

    public function testRejectsSpecialCharacters()
    {
        $validator = new Digits();
        $this->assertFalse($validator->validate('123!'));
    }
}
