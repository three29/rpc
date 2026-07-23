<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\IsEmpty;

class IsEmptyTest extends TestCase
{
    public function testValidatesEmptyString()
    {
        $validator = new IsEmpty();
        $this->assertTrue($validator->validate(''));
    }

    public function testValidatesNull()
    {
        $validator = new IsEmpty();
        $this->assertTrue($validator->validate(null));
    }

    public function testValidatesZero()
    {
        $validator = new IsEmpty();
        $this->assertTrue($validator->validate(0));
    }

    public function testValidatesZeroString()
    {
        $validator = new IsEmpty();
        $this->assertTrue($validator->validate('0'));
    }

    public function testValidatesFalse()
    {
        $validator = new IsEmpty();
        $this->assertTrue($validator->validate(false));
    }

    public function testValidatesEmptyArray()
    {
        $validator = new IsEmpty();
        $this->assertTrue($validator->validate(array()));
    }

    public function testRejectsNonEmptyString()
    {
        $validator = new IsEmpty();
        $this->assertFalse($validator->validate('test'));
    }

    public function testRejectsNonZeroNumber()
    {
        $validator = new IsEmpty();
        $this->assertFalse($validator->validate(123));
    }

    public function testRejectsTrue()
    {
        $validator = new IsEmpty();
        $this->assertFalse($validator->validate(true));
    }

    public function testRejectsNonEmptyArray()
    {
        $validator = new IsEmpty();
        $this->assertFalse($validator->validate(array('test')));
    }
}
