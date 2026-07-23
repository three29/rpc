<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Alternation;
use RPC\Validator\Alpha;
use RPC\Validator\Integer;
use RPC\Validator\Email;

class AlternationTest extends TestCase
{
    public function testValidatesWhenFirstValidatorPasses()
    {
        $validator = new Alternation(
            new Alpha(),
            new Integer()
        );

        $this->assertTrue($validator->validate('abc'));
    }

    public function testValidatesWhenSecondValidatorPasses()
    {
        $validator = new Alternation(
            new Alpha(),
            new Integer()
        );

        $this->assertTrue($validator->validate(123));
    }

    public function testValidatesWithMultipleValidators()
    {
        $validator = new Alternation(
            new Alpha(),
            new Integer(),
            new Email()
        );

        $this->assertTrue($validator->validate('test@example.com'));
    }

    public function testFailsWhenAllValidatorsFail()
    {
        $validator = new Alternation(
            new Alpha(),
            new Integer()
        );

        $this->assertFalse($validator->validate('abc123'));
    }

    public function testStoresFirstErrorMessage()
    {
        $validator = new Alternation(
            new Alpha('Must be alphabetic'),
            new Integer('Must be integer')
        );

        $validator->validate('abc123');
        $this->assertEquals('Must be alphabetic', $validator->getError());
    }

    public function testAddValidatorMethod()
    {
        $validator = new Alternation();
        $validator->add(new Alpha());
        $validator->add(new Integer());

        $this->assertTrue($validator->validate('abc'));
        $this->assertTrue($validator->validate(123));
        $this->assertFalse($validator->validate('abc123'));
    }

    public function testConstructorWithValidators()
    {
        $validator = new Alternation(
            new Alpha(),
            new Integer()
        );

        $this->assertTrue($validator->validate('test'));
    }

    public function testEmptyValidatorList()
    {
        $validator = new Alternation();
        $this->assertFalse($validator->validate('anything'));
    }
}
