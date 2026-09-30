<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Equal;

class EqualTest extends UnitTestCase
{
    public function testLooseEquality(): void
    {
        $validator = new Equal('123', false, 'Must equal 123');

        $this->assertTrue($validator->validate('123'));
        $this->assertTrue($validator->validate(123)); // loose comparison
    }

    public function testStrictEquality(): void
    {
        $validator = new Equal('123', true, 'Must strictly equal "123"');

        $this->assertTrue($validator->validate('123'));
        $this->assertFalse($validator->validate(123)); // strict comparison fails
    }

    public function testEqualityWithDifferentTypes(): void
    {
        $looseValidator = new Equal(0, false);
        $strictValidator = new Equal(0, true);

        // Loose comparison: 0 == false is true, but 0 == '' is false
        $this->assertTrue($looseValidator->validate(false));
        $this->assertTrue($looseValidator->validate('0')); // '0' == 0 is true
        $this->assertFalse($looseValidator->validate('')); // '' == 0 is false

        // Strict comparison: 0 !== false
        $this->assertFalse($strictValidator->validate(false));
        $this->assertFalse($strictValidator->validate('0'));
    }

    public function testEqualityWithNull(): void
    {
        $validator = new Equal(null, true);

        $this->assertTrue($validator->validate(null));
        $this->assertFalse($validator->validate(0));
        $this->assertFalse($validator->validate(''));
    }

    public function testErrorMessage(): void
    {
        $message = 'Values must be equal';
        $validator = new Equal('test', false, $message);
        $this->assertEquals($message, $validator->getError());
    }
}
