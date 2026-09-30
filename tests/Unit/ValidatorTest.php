<?php

namespace Tests\Unit;

use RPC\Validator;

class ValidatorTest extends UnitTestCase
{
    public function testValidatorIsAbstract(): void
    {
        $reflection = new \ReflectionClass(Validator::class);
        $this->assertTrue($reflection->isAbstract());
    }

    public function testValidatorHasAbstractValidateMethod(): void
    {
        $reflection = new \ReflectionClass(Validator::class);
        $method = $reflection->getMethod('validate');
        $this->assertTrue($method->isAbstract());
    }

    public function testConcreteValidatorCanSetAndGetError(): void
    {
        // Use a concrete implementation to test error message functionality
        $validator = new \RPC\Validator\NotEmpty('Test error message');

        $this->assertEquals('Test error message', $validator->getError());

        $validator->setError('New error message');
        $this->assertEquals('New error message', $validator->getError());
    }

    public function testValidatorConstructorSetsError(): void
    {
        $errorMessage = 'Custom error';
        $validator = new \RPC\Validator\Alpha($errorMessage);

        $this->assertEquals($errorMessage, $validator->getError());
    }
}
