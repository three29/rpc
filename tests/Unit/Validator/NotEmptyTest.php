<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\NotEmpty;

class NotEmptyTest extends UnitTestCase
{
    private NotEmpty $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new NotEmpty('This field cannot be empty');
    }

    public function testNotEmptyValues(): void
    {
        $this->assertTrue($this->validator->validate('test'), 'String should not be empty');
        // Note: '0' is considered empty by PHP's empty() function
        // This is expected PHP behavior
        $this->assertTrue($this->validator->validate(1), 'Integer 1 should not be empty');
        $this->assertTrue($this->validator->validate([1, 2, 3]), 'Array with values should not be empty');
        $this->assertTrue($this->validator->validate(['key' => 'value']), 'Array with key-value should not be empty');
    }

    public function testStringZeroIsEmpty(): void
    {
        // PHP's empty() treats '0' as empty, which is expected behavior
        $this->assertFalse($this->validator->validate('0'));
    }

    public function testEmptyValues(): void
    {
        $emptyValues = [
            '',
            0,
            null,
            false,
            [],
        ];

        foreach ($emptyValues as $value) {
            $this->assertFalse(
                $this->validator->validate($value),
                "Expected value to be empty"
            );
        }
    }

    public function testTrueIsNotEmpty(): void
    {
        // true is truthy and should not be considered empty by NotEmpty validator
        // This is a special case that behaves differently than other values
        $this->assertTrue($this->validator->validate(true));
    }

    public function testErrorMessage(): void
    {
        $message = 'Required field';
        $validator = new NotEmpty($message);
        $this->assertEquals($message, $validator->getError());
    }
}
