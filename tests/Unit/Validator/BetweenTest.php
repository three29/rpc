<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Between;

class BetweenTest extends UnitTestCase
{
    public function testValuesBetweenRange(): void
    {
        $validator = new Between(10, 20, 'Value must be between 10 and 20');

        $validValues = [10, 15, 20, 10.5, 19.9];

        foreach ($validValues as $value) {
            $this->assertTrue(
                $validator->validate($value),
                "Expected {$value} to be between 10 and 20"
            );
        }
    }

    public function testValuesOutsideRange(): void
    {
        $validator = new Between(10, 20);

        $invalidValues = [9, 9.9, 20.1, 21, 0, 100, -10];

        foreach ($invalidValues as $value) {
            $this->assertFalse(
                $validator->validate($value),
                "Expected {$value} to be outside range 10-20"
            );
        }
    }

    public function testNonNumericValues(): void
    {
        $validator = new Between(1, 10);

        $nonNumericValues = ['string', null, true, false, []];

        foreach ($nonNumericValues as $value) {
            $this->assertFalse(
                $validator->validate($value),
                "Expected non-numeric value to fail validation"
            );
        }
    }

    public function testNegativeRange(): void
    {
        $validator = new Between(-10, -5);

        $this->assertTrue($validator->validate(-7));
        $this->assertTrue($validator->validate(-10));
        $this->assertTrue($validator->validate(-5));
        $this->assertFalse($validator->validate(-11));
        $this->assertFalse($validator->validate(0));
    }

    public function testConstructorRequiresMinMax(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid arguments');

        new Between(null, 10);
    }

    public function testConstructorRequiresMaximum(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid arguments');

        new Between(10, null);
    }

    public function testErrorMessage(): void
    {
        $message = 'Value out of range';
        $validator = new Between(1, 100, $message);
        $this->assertEquals($message, $validator->getError());
    }
}
