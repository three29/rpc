<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Hex;

class HexTest extends TestCase
{
    public function testValidatesLowercaseHex()
    {
        $validator = new Hex();
        $this->assertTrue($validator->validate('abc123'));
    }

    public function testValidatesUppercaseHex()
    {
        $validator = new Hex();
        $this->assertTrue($validator->validate('ABC123'));
    }

    public function testValidatesMixedCaseHex()
    {
        $validator = new Hex();
        $this->assertTrue($validator->validate('AbC123'));
    }

    public function testValidatesAllDigits()
    {
        $validator = new Hex();
        $this->assertTrue($validator->validate('123456'));
    }

    public function testValidatesAllLetters()
    {
        $validator = new Hex();
        $this->assertTrue($validator->validate('abcdef'));
    }

    public function testValidatesColorCode()
    {
        $validator = new Hex();
        $this->assertTrue($validator->validate('FF5733'));
    }

    public function testRejectsInvalidHexCharacters()
    {
        $validator = new Hex();
        $this->assertFalse($validator->validate('xyz123'));
    }

    public function testRejectsSpecialCharacters()
    {
        $validator = new Hex();
        $this->assertFalse($validator->validate('abc!23'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Hex();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsWhitespace()
    {
        $validator = new Hex();
        $this->assertFalse($validator->validate('abc 123'));
    }
}
