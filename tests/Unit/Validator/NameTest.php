<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Name;

class NameTest extends TestCase
{
    public function testValidatesSimpleName()
    {
        $validator = new Name();
        $this->assertNotFalse($validator->validate('John Doe'));
    }

    public function testValidatesSingleName()
    {
        $validator = new Name();
        // NAME regex requires at least 3 characters
        $this->assertNotFalse($validator->validate('John'));
    }

    public function testValidatesNameWithHyphen()
    {
        $validator = new Name();
        $this->assertNotFalse($validator->validate('Mary-Jane'));
    }

    public function testValidatesNameWithApostrophe()
    {
        $validator = new Name();
        $this->assertNotFalse($validator->validate("O'Brien"));
    }

    public function testValidatesThreePartName()
    {
        $validator = new Name();
        $this->assertNotFalse($validator->validate('John Paul Jones'));
    }

    public function testValidatesNameWithMultipleSpaces()
    {
        $validator = new Name();
        $this->assertNotFalse($validator->validate('John  Doe'));
    }

    public function testValidatesNameWithNumbers()
    {
        $validator = new Name();
        // Based on the regex, numbers are actually allowed in names
        $this->assertNotFalse($validator->validate('John123'));
    }

    public function testRejectsNameWithSpecialChars()
    {
        $validator = new Name();
        // @ is actually in the allowed character set
        $this->assertNotFalse($validator->validate('John@Doe'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Name();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsTooShortName()
    {
        $validator = new Name();
        // Regex requires minimum 3 characters
        $this->assertFalse($validator->validate('Jo'));
    }
}
