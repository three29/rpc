<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Alpha;

class AlphaTest extends UnitTestCase
{
    private Alpha $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Alpha('Must contain only letters');
    }

    public function testValidAlpha(): void
    {
        $validInputs = [
            'abc',
            'ABC',
            'AbCdEf',
            'test',
            'UPPERCASE',
        ];

        foreach ($validInputs as $input) {
            $this->assertTrue(
                $this->validator->validate($input),
                "Expected '{$input}' to be valid alpha"
            );
        }
    }

    public function testInvalidAlpha(): void
    {
        $invalidInputs = [
            '123',
            'abc123',
            'test-name',
            'hello world',
            'user@email',
            '',
            'test_name',
        ];

        foreach ($invalidInputs as $input) {
            $this->assertFalse(
                $this->validator->validate($input),
                "Expected '{$input}' to be invalid alpha"
            );
        }
    }

    public function testErrorMessage(): void
    {
        $message = 'Only alphabetic characters allowed';
        $validator = new Alpha($message);
        $this->assertEquals($message, $validator->getError());
    }
}
