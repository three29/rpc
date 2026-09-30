<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\OneOf;

class OneOfTest extends TestCase
{
    public function testValidatesValueInArray()
    {
        $validator = new OneOf(['apple', 'banana', 'orange']);
        $this->assertTrue($validator->validate('apple'));
    }

    public function testValidatesAnotherValueInArray()
    {
        $validator = new OneOf(['apple', 'banana', 'orange']);
        $this->assertTrue($validator->validate('banana'));
    }

    public function testValidatesNumericValueInArray()
    {
        $validator = new OneOf([1, 2, 3, 4, 5]);
        $this->assertTrue($validator->validate(3));
    }

    public function testRejectsValueNotInArray()
    {
        $validator = new OneOf(['apple', 'banana', 'orange']);
        $this->assertFalse($validator->validate('grape'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new OneOf(['apple', 'banana', 'orange']);
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsNumericValueNotInArray()
    {
        $validator = new OneOf([1, 2, 3, 4, 5]);
        $this->assertFalse($validator->validate(10));
    }

    public function testValidatesWithLooseComparison()
    {
        $validator = new OneOf([1, 2, 3]);
        // Uses == comparison, so '2' should match 2
        $this->assertTrue($validator->validate('2'));
    }

    public function testWorksWithObject()
    {
        $obj = new \stdClass();
        $obj->a = 'apple';
        $obj->b = 'banana';
        $obj->c = 'orange';

        $validator = new OneOf($obj);
        $this->assertTrue($validator->validate('banana'));
    }

    public function testThrowsExceptionForInvalidParameter()
    {
        $this->expectException(\TypeError::class);
        new OneOf('string');
    }

    public function testErrorMessage()
    {
        $message = 'Value must be one of the allowed values';
        $validator = new OneOf(['a', 'b', 'c'], $message);
        $this->assertEquals($message, $validator->getError());
    }
}
