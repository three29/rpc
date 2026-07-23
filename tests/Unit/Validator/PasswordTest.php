<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Password;

class PasswordTest extends TestCase
{
    public function testValidatesValidPassword()
    {
        $validator = new Password();
        // Password regex only requires 6-32 characters (const PASSWORD = '/^.{6,32}$/';)
        $this->assertTrue((bool)$validator->validate('Password123'));
    }

    public function testValidatesPasswordWithSpecialChars()
    {
        $validator = new Password();
        $this->assertTrue((bool)$validator->validate('Pass@word123'));
    }

    public function testValidatesComplexPassword()
    {
        $validator = new Password();
        $this->assertTrue((bool)$validator->validate('C0mpl3x!Pass'));
    }

    public function testRejectsShortPassword()
    {
        $validator = new Password();
        // Less than 6 characters - preg_match returns false, not 0
        $this->assertFalse($validator->validate('Pass1'));
    }

    public function testValidatesPasswordWithoutNumber()
    {
        $validator = new Password();
        // Current regex doesn't require numbers, just 6-32 chars
        $this->assertTrue((bool)$validator->validate('Password'));
    }

    public function testValidatesPasswordWithoutUppercase()
    {
        $validator = new Password();
        // Current regex doesn't require uppercase
        $this->assertTrue((bool)$validator->validate('password123'));
    }

    public function testValidatesPasswordWithoutLowercase()
    {
        $validator = new Password();
        // Current regex doesn't require lowercase
        $this->assertTrue((bool)$validator->validate('PASSWORD123'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Password();
        // Empty string - preg_match returns false, not 0
        $this->assertFalse($validator->validate(''));
    }

    public function testValidatesSimplePassword()
    {
        $validator = new Password();
        // Current regex allows this (8 chars)
        $this->assertTrue((bool)$validator->validate('12345678'));
    }

    public function testRejectsTooLongPassword()
    {
        $validator = new Password();
        // More than 32 characters - preg_match returns false, not 0
        $this->assertFalse($validator->validate(str_repeat('a', 33)));
    }
}
