<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Length;

class LengthTest extends UnitTestCase
{
    public function testLengthWithinRange(): void
    {
        $validator = new Length(3, 10, 'Length must be between 3 and 10');

        $this->assertTrue($validator->validate('abc'));
        $this->assertTrue($validator->validate('test'));
        $this->assertTrue($validator->validate('1234567890'));
    }

    public function testLengthOutsideRange(): void
    {
        $validator = new Length(3, 10, 'Length must be between 3 and 10');

        $this->assertFalse($validator->validate('ab')); // too short
        $this->assertFalse($validator->validate('12345678901')); // too long
        $this->assertFalse($validator->validate('')); // empty
    }

    public function testMinLengthOnly(): void
    {
        $validator = new Length(5, -1, 'Minimum 5 characters');

        $this->assertTrue($validator->validate('12345'));
        $this->assertTrue($validator->validate('123456789'));
        $this->assertFalse($validator->validate('1234'));
    }

    public function testMaxLengthOnly(): void
    {
        $validator = new Length(-1, 5, 'Maximum 5 characters');

        $this->assertTrue($validator->validate(''));
        $this->assertTrue($validator->validate('12345'));
        $this->assertFalse($validator->validate('123456'));
    }

    public function testExceptionWhenBothZero(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Illegal arguments');

        $validator = new Length(0, 0);
        $validator->validate('test');
    }

    public function testErrorMessage(): void
    {
        $message = 'Length error';
        $validator = new Length(1, 10, $message);
        $this->assertEquals($message, $validator->getError());
    }
}
