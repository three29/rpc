<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Chain;
use RPC\Validator\NotEmpty;
use RPC\Validator\Length;
use RPC\Validator\Alpha;
use RPC\Validator\Integer;

class ChainTest extends TestCase
{
    public function testValidatesWhenAllValidatorsPass()
    {
        $validator = new Chain(
            new NotEmpty(),
            new Alpha()
        );

        $this->assertTrue($validator->validate('abc'));
    }

    public function testFailsWhenFirstValidatorFails()
    {
        $validator = new Chain(
            new NotEmpty(),
            new Alpha()
        );

        $this->assertFalse($validator->validate(''));
    }

    public function testFailsWhenSecondValidatorFails()
    {
        $validator = new Chain(
            new NotEmpty(),
            new Alpha()
        );

        $this->assertFalse($validator->validate('abc123'));
    }

    public function testBreaksOnFirstError()
    {
        $validator = new Chain(
            new NotEmpty('Cannot be empty'),
            new Alpha('Must be alphabetic'),
            new Length(5, 10, 'Must be 5-10 characters')
        );

        $validator->validate('');
        // Should get error from first validator, not second or third
        $this->assertEquals('Cannot be empty', $validator->getError());
    }

    public function testValidatesWithMultipleValidators()
    {
        $validator = new Chain(
            new NotEmpty(),
            new Alpha(),
            new Length(3, 10)
        );

        $this->assertTrue($validator->validate('test'));
        $this->assertFalse($validator->validate('ab')); // Too short
    }

    public function testAddValidatorMethod()
    {
        $validator = new Chain();
        $validator->add(new NotEmpty());
        $validator->add(new Alpha());

        $this->assertTrue($validator->validate('test'));
        $this->assertFalse($validator->validate(''));
    }

    public function testAddReturnsChainForFluency()
    {
        $validator = new Chain();
        $result = $validator->add(new NotEmpty());

        $this->assertInstanceOf(Chain::class, $result);
        $this->assertSame($validator, $result);
    }

    public function testConstructorWithValidators()
    {
        $validator = new Chain(
            new NotEmpty(),
            new Alpha()
        );

        $this->assertTrue($validator->validate('test'));
    }

    public function testEmptyChainPassesValidation()
    {
        $validator = new Chain();
        $this->assertTrue($validator->validate('anything'));
    }

    public function testStoresCorrectErrorMessage()
    {
        $validator = new Chain(
            new NotEmpty('Value required'),
            new Integer('Must be integer')
        );

        $validator->validate('abc');
        $this->assertEquals('Must be integer', $validator->getError());
    }
}
