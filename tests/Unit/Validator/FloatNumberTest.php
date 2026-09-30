<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\FloatNumber;

class FloatNumberTest extends UnitTestCase
{
    private FloatNumber $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new FloatNumber('Must be float');
    }

    public function testValidFloat(): void
    {
        $this->assertTrue($this->validator->validate(1.23));
        $this->assertTrue($this->validator->validate(0.0));
        $this->assertTrue($this->validator->validate(-1.5));
    }

    public function testInvalidFloat(): void
    {
        // Note: is_float() is strict - it only returns true for actual float types
        $this->assertFalse($this->validator->validate(123)); // integer
        $this->assertFalse($this->validator->validate('1.23')); // string
        $this->assertFalse($this->validator->validate('abc'));
        $this->assertFalse($this->validator->validate([]));
        $this->assertFalse($this->validator->validate(null));
    }

    public function testErrorMessage(): void
    {
        $message = 'Must be float';
        $validator = new FloatNumber($message);
        $this->assertEquals($message, $validator->getError());
    }
}
