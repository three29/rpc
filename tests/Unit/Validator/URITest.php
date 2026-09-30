<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\URI;

class URITest extends TestCase
{
    public function testValidatesHttpUrl()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('http://example.com'));
    }

    public function testValidatesHttpsUrl()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('https://example.com'));
    }

    public function testValidatesUrlWithPath()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('https://example.com/path/to/page'));
    }

    public function testValidatesUrlWithQueryString()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('https://example.com?param=value'));
    }

    public function testValidatesUrlWithFragment()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('https://example.com#section'));
    }

    public function testValidatesUrlWithPort()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('https://example.com:8080'));
    }

    public function testValidatesUrlWithSubdomain()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('https://subdomain.example.com'));
    }

    public function testValidatesFtpUrl()
    {
        $validator = new URI();
        $this->assertNotFalse($validator->validate('ftp://example.com'));
    }

    public function testRejectsInvalidUrl()
    {
        $validator = new URI();
        $this->assertFalse($validator->validate('not a url'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new URI();
        $this->assertFalse($validator->validate(''));
    }
}
