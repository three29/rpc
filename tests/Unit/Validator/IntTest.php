<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Integer;

class IntTest extends UnitTestCase
{
    private \RPC\Validator\Integer $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Integer('Must be an integer');
    }

    public function testValidIntegers(): void
    {
        $validIntegers = [
            0,
            1,
            -1,
            100,
            -100,
            PHP_INT_MAX,
            PHP_INT_MIN,
        ];

        foreach ($validIntegers as $value) {
            $this->assertTrue(
                $this->validator->validate($value),
                "Expected {$value} to be a valid integer"
            );
        }
    }

    public function testInvalidIntegers(): void
    {
        $invalidValues = [
            1.5,
            '123',
            '0',
            true,
            false,
            null,
            [],
            'string',
        ];

        foreach ($invalidValues as $value) {
            $this->assertFalse(
                $this->validator->validate($value),
                "Expected value to not be a valid integer"
            );
        }
    }

    public function testErrorMessage(): void
    {
        $message = 'Integer value required';
        $validator = new \RPC\Validator\Integer($message);
        $this->assertEquals($message, $validator->getError());
    }
}
