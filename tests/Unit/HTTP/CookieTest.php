<?php

namespace Tests\Unit\HTTP;

use PHPUnit\Framework\TestCase;
use RPC\HTTP\Cookie;

class CookieTest extends TestCase
{
    public function testConstructorWithDefaults(): void
    {
        $cookie = new Cookie('test_cookie');

        $this->assertSame('test_cookie', $cookie->getName());
        $this->assertSame('', $cookie->getValue());
        $this->assertSame(0, $cookie->getExpire());
        $this->assertSame('', $cookie->getPath());
        $this->assertSame('', $cookie->getDomain());
        $this->assertFalse($cookie->isSecure());
        $this->assertFalse($cookie->isHTTPOnly());
    }

    public function testConstructorWithAllParameters(): void
    {
        $cookie = new Cookie(
            'session_id',
            'abc123',
            time() + 3600,
            '/admin',
            'example.com',
            true,
            true
        );

        $this->assertSame('session_id', $cookie->getName());
        $this->assertSame('abc123', $cookie->getValue());
        $this->assertGreaterThan(0, $cookie->getExpire());
        $this->assertSame('/admin', $cookie->getPath());
        $this->assertSame('example.com', $cookie->getDomain());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHTTPOnly());
    }

    public function testSetName(): void
    {
        $cookie = new Cookie('old_name');
        $result = $cookie->setName('new_name');

        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertSame('new_name', $cookie->getName());
    }

    public function testSetValue(): void
    {
        $cookie = new Cookie('test');
        $result = $cookie->setValue('new_value');

        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertSame('new_value', $cookie->getValue());
    }

    public function testSetExpire(): void
    {
        $cookie = new Cookie('test');
        $expire = time() + 7200;
        $result = $cookie->setExpire($expire);

        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertSame($expire, $cookie->getExpire());
    }

    public function testSetPath(): void
    {
        $cookie = new Cookie('test');
        $result = $cookie->setPath('/dashboard');

        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertSame('/dashboard', $cookie->getPath());
    }

    public function testSetDomain(): void
    {
        $cookie = new Cookie('test');
        $result = $cookie->setDomain('subdomain.example.com');

        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertSame('subdomain.example.com', $cookie->getDomain());
    }

    public function testSetSecure(): void
    {
        $cookie = new Cookie('test');

        $result = $cookie->setSecure(true);
        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertTrue($cookie->isSecure());

        $cookie->setSecure(false);
        $this->assertFalse($cookie->isSecure());

        // Test type casting
        $cookie->setSecure(1);
        $this->assertTrue($cookie->isSecure());

        $cookie->setSecure(0);
        $this->assertFalse($cookie->isSecure());
    }

    public function testSetHTTPOnly(): void
    {
        $cookie = new Cookie('test');

        $result = $cookie->setHTTPOnly(true);
        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertTrue($cookie->isHTTPOnly());

        $cookie->setHTTPOnly(false);
        $this->assertFalse($cookie->isHTTPOnly());

        // Test type casting
        $cookie->setHTTPOnly(1);
        $this->assertTrue($cookie->isHTTPOnly());

        $cookie->setHTTPOnly(0);
        $this->assertFalse($cookie->isHTTPOnly());
    }

    public function testFluentInterface(): void
    {
        $cookie = new Cookie('test');

        $result = $cookie
            ->setName('fluent_test')
            ->setValue('fluent_value')
            ->setExpire(3600)
            ->setPath('/api')
            ->setDomain('api.example.com')
            ->setSecure(true)
            ->setHTTPOnly(true);

        $this->assertInstanceOf(Cookie::class, $result);
        $this->assertSame('fluent_test', $cookie->getName());
        $this->assertSame('fluent_value', $cookie->getValue());
        $this->assertSame(3600, $cookie->getExpire());
        $this->assertSame('/api', $cookie->getPath());
        $this->assertSame('api.example.com', $cookie->getDomain());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHTTPOnly());
    }
}
