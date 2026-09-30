<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Zip;

class ZipTest extends TestCase
{
    public function testValidates5DigitZip()
    {
        $validator = new Zip();
        $this->assertTrue($validator->validate('12345'));
    }

    public function testValidatesZipPlus4()
    {
        $validator = new Zip();
        $this->assertTrue($validator->validate('12345-6789'));
    }

    public function testValidatesZipStartingWithZero()
    {
        $validator = new Zip();
        $this->assertTrue($validator->validate('01234'));
    }

    public function testRejectsTooFewDigits()
    {
        $validator = new Zip();
        $this->assertFalse($validator->validate('1234'));
    }

    public function testRejectsTooManyDigits()
    {
        $validator = new Zip();
        $this->assertFalse($validator->validate('123456'));
    }

    public function testRejectsAlphabeticCharacters()
    {
        $validator = new Zip();
        $this->assertFalse($validator->validate('abcde'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Zip();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsInvalidPlus4Format()
    {
        $validator = new Zip();
        $this->assertFalse($validator->validate('12345-678'));
    }

    public function testRejectsZipWithSpaces()
    {
        $validator = new Zip();
        $this->assertFalse($validator->validate('123 45'));
    }
}
