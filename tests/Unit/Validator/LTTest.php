<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\LT;

class LTTest extends TestCase
{
    public function testValidatesValueLessThanMax()
    {
        $validator = new LT(10);
        $this->assertTrue($validator->validate(5));
    }

    public function testValidatesValueJustBelowMax()
    {
        $validator = new LT(10);
        $this->assertTrue($validator->validate(9));
    }

    public function testValidatesNegativeValue()
    {
        $validator = new LT(10);
        $this->assertTrue($validator->validate(-100));
    }

    public function testRejectsValueEqualToMax()
    {
        $validator = new LT(10);
        $this->assertFalse($validator->validate(10));
    }

    public function testRejectsValueGreaterThanMax()
    {
        $validator = new LT(10);
        $this->assertFalse($validator->validate(15));
    }

    public function testRejectsValueMuchGreaterThanMax()
    {
        $validator = new LT(10);
        $this->assertFalse($validator->validate(1000));
    }

    public function testWorksWithNegativeMax()
    {
        $validator = new LT(-5);
        $this->assertTrue($validator->validate(-10));
        $this->assertFalse($validator->validate(0));
    }

    public function testWorksWithFloatValues()
    {
        $validator = new LT(10.5);
        $this->assertTrue($validator->validate(10.4));
        $this->assertFalse($validator->validate(10.6));
    }

    public function testErrorMessage()
    {
        $message = 'Value must be less than 10';
        $validator = new LT(10, $message);
        $this->assertEquals($message, $validator->getError());
    }
}
