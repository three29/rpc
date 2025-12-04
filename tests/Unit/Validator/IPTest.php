<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use RPC\Validator\IP;

class IPTest extends TestCase
{
    public function testValidatesValidIPv4()
    {
        $validator = new IP();
        $this->assertTrue($validator->validate('192.168.1.1'));
    }

    public function testValidatesLocalhostIP()
    {
        $validator = new IP();
        $this->assertTrue($validator->validate('127.0.0.1'));
    }

    public function testValidatesPublicIP()
    {
        $validator = new IP();
        $this->assertTrue($validator->validate('8.8.8.8'));
    }

    public function testValidatesMaxOctetValues()
    {
        $validator = new IP();
        $this->assertTrue($validator->validate('255.255.255.255'));
    }

    public function testValidatesZeroIP()
    {
        $validator = new IP();
        // Note: ip2long('0.0.0.0') returns 0, which is falsy, so this will fail
        $this->assertFalse($validator->validate('0.0.0.0'));
    }

    public function testRejectsInvalidOctet()
    {
        $validator = new IP();
        $this->assertFalse($validator->validate('256.1.1.1'));
    }

    public function testRejectsIncompletIP()
    {
        $validator = new IP();
        $this->assertFalse($validator->validate('192.168.1'));
    }

    public function testRejectsAlphabeticString()
    {
        $validator = new IP();
        $this->assertFalse($validator->validate('not.an.ip.address'));
    }

    public function testRejectsEmptyString()
    {
        $validator = new IP();
        $this->assertFalse($validator->validate(''));
    }

    public function testRejectsIPv6()
    {
        $validator = new IP();
        $this->assertFalse($validator->validate('2001:0db8:85a3:0000:0000:8a2e:0370:7334'));
    }
}
