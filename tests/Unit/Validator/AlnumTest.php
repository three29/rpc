<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Alnum;

class AlnumTest extends UnitTestCase
{
    private Alnum $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Alnum('Must be alphanumeric');
    }

    public function testValidAlphanumeric(): void
    {
        $this->assertTrue($this->validator->validate('abc123'));
        $this->assertTrue($this->validator->validate('ABC'));
        $this->assertTrue($this->validator->validate('123'));
        $this->assertTrue($this->validator->validate('Test123'));
    }

    public function testInvalidAlphanumeric(): void
    {
        $this->assertFalse($this->validator->validate('abc 123')); // space
        $this->assertFalse($this->validator->validate('test-123')); // hyphen
        $this->assertFalse($this->validator->validate('test_123')); // underscore
        $this->assertFalse($this->validator->validate('test@123')); // special char
        $this->assertFalse($this->validator->validate('')); // empty
    }

    public function testErrorMessage(): void
    {
        $message = 'Must be alphanumeric';
        $validator = new Alnum($message);
        $this->assertEquals($message, $validator->getError());
    }
}
