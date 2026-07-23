<?php

namespace Tests\Unit\Validator;

use Tests\Unit\UnitTestCase;
use RPC\Validator\Email;

class EmailTest extends UnitTestCase
{
    private Email $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Email('Invalid email format');
    }

    public function testValidEmails(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@example.com',
            'user+tag@example.co.uk',
            'user_name@example.org',
            'test123@test-domain.com',
        ];

        foreach ($validEmails as $email) {
            $this->assertNotEquals(
                0,
                $this->validator->validate($email),
                "Expected '{$email}' to be valid"
            );
        }
    }

    public function testInvalidEmails(): void
    {
        $invalidEmails = [
            'invalid',
            '@example.com',
            'user@',
            'user @example.com',
            'user@.com',
            '',
        ];

        foreach ($invalidEmails as $email) {
            $this->assertEquals(
                0,
                $this->validator->validate($email),
                "Expected '{$email}' to be invalid"
            );
        }
    }

    public function testErrorMessage(): void
    {
        $message = 'Custom error message';
        $validator = new Email($message);
        $this->assertEquals($message, $validator->getError());
    }
}
