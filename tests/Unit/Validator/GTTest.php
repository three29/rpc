<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\GT;

class GTTest extends TestCase
{
    public function testValidatesValueGreaterThanMin()
    {
        $validator = new GT(10);
        $this->assertTrue($validator->validate(15));
    }

    public function testValidatesValueJustAboveMin()
    {
        $validator = new GT(10);
        $this->assertTrue($validator->validate(11));
    }

    public function testValidatesLargeValue()
    {
        $validator = new GT(10);
        $this->assertTrue($validator->validate(1000));
    }

    public function testRejectsValueEqualToMin()
    {
        $validator = new GT(10);
        $this->assertFalse($validator->validate(10));
    }

    public function testRejectsValueLessThanMin()
    {
        $validator = new GT(10);
        $this->assertFalse($validator->validate(5));
    }

    public function testRejectsValueMuchLessThanMin()
    {
        $validator = new GT(10);
        $this->assertFalse($validator->validate(-100));
    }

    public function testWorksWithNegativeMin()
    {
        $validator = new GT(-5);
        $this->assertTrue($validator->validate(0));
        $this->assertFalse($validator->validate(-10));
    }

    public function testWorksWithFloatValues()
    {
        $validator = new GT(10.5);
        $this->assertTrue($validator->validate(10.6));
        $this->assertFalse($validator->validate(10.4));
    }

    public function testErrorMessage()
    {
        $message = 'Value must be greater than 10';
        $validator = new GT(10, $message);
        $this->assertEquals($message, $validator->getError());
    }
}
