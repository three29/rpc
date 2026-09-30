<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\IsNumeric;

class NumericTest extends UnitTestCase
{
    private IsNumeric $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new IsNumeric('Must be numeric');
    }

    public function testValidNumeric(): void
    {
        $this->assertTrue($this->validator->validate(123));
        $this->assertTrue($this->validator->validate(1.23));
        $this->assertTrue($this->validator->validate('123'));
        $this->assertTrue($this->validator->validate('1.23'));
        $this->assertTrue($this->validator->validate('-123'));
        $this->assertTrue($this->validator->validate('0'));
    }

    public function testInvalidNumeric(): void
    {
        $this->assertFalse($this->validator->validate('abc'));
        $this->assertFalse($this->validator->validate('12abc'));
        $this->assertFalse($this->validator->validate(''));
        $this->assertFalse($this->validator->validate([]));
        $this->assertFalse($this->validator->validate(null));
    }

    public function testErrorMessage(): void
    {
        $message = 'Must be numeric';
        $validator = new IsNumeric($message);
        $this->assertEquals($message, $validator->getError());
    }
}
