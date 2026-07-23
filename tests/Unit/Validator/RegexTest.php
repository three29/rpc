<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Regex;

class RegexTest extends UnitTestCase
{
    public function testValidPattern(): void
    {
        $validator = new Regex('/^[A-Z][a-z]+$/', 'Must be capitalized word');

        $this->assertNotEquals(0, $validator->validate('Hello'));
        $this->assertNotEquals(0, $validator->validate('World'));
    }

    public function testInvalidPattern(): void
    {
        $validator = new Regex('/^[A-Z][a-z]+$/', 'Must be capitalized word');

        $this->assertEquals(0, $validator->validate('hello'));
        $this->assertEquals(0, $validator->validate('HELLO'));
        $this->assertEquals(0, $validator->validate('123'));
    }

    public function testIntegerConversion(): void
    {
        $validator = new Regex('/^\d+$/', 'Must be digits');

        // Integers should be converted to strings
        $this->assertNotEquals(0, $validator->validate(123));
    }

    public function testNonStringReturnsFalse(): void
    {
        $validator = new Regex('/test/', 'Pattern test');

        $this->assertFalse($validator->validate([]));
        $this->assertFalse($validator->validate(null));
    }

    public function testExceptionOnEmptyPattern(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You must supply a valid pattern');

        new Regex('');
    }

    public function testErrorMessage(): void
    {
        $message = 'Pattern error';
        $validator = new Regex('/test/', $message);
        $this->assertEquals($message, $validator->getError());
    }
}
