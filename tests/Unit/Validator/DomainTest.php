<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\Domain;

class DomainTest extends TestCase
{
    public function testValidatesSimpleDomain()
    {
        $validator = new Domain();
        $this->assertNotFalse($validator->validate('example.com'));
    }

    public function testValidatesSubdomain()
    {
        $validator = new Domain();
        $this->assertNotFalse($validator->validate('subdomain.example.com'));
    }

    public function testValidatesMultiLevelSubdomain()
    {
        $validator = new Domain();
        $this->assertNotFalse($validator->validate('deep.subdomain.example.com'));
    }

    public function testValidatesDomainWithHyphen()
    {
        $validator = new Domain();
        $this->assertNotFalse($validator->validate('my-domain.com'));
    }

    public function testValidatesDomainWithNumbers()
    {
        $validator = new Domain();
        $this->assertNotFalse($validator->validate('domain123.com'));
    }

    public function testValidatesDifferentTlds()
    {
        $validator = new Domain();
        $this->assertNotFalse($validator->validate('example.org'));
        $this->assertNotFalse($validator->validate('example.net'));
        $this->assertNotFalse($validator->validate('example.co.uk'));
    }

    public function testRejectsInvalidCharacters()
    {
        $validator = new Domain();
        $this->assertFalse($validator->validate('invalid_domain.com'));
    }

    public function testRejectsSpaces()
    {
        $validator = new Domain();
        $this->assertFalse($validator->validate('my domain.com'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new Domain();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsNoDotTld()
    {
        $validator = new Domain();
        $this->assertFalse($validator->validate('nodottld'));
    }
}
