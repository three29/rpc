<?php

namespace Tests\Unit\HTTP;

use Tests\Unit\UnitTestCase;
use RPC\HTTP\Request;

class RequestTest extends UnitTestCase
{
    private Request $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock superglobals for testing
        $_POST = [];
        $_GET = [];
        $_FILES = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['QUERY_STRING'] = '';

        $this->request = Request::getInstance();
    }

    public function testGetInstance(): void
    {
        $instance1 = Request::getInstance();
        $instance2 = Request::getInstance();

        $this->assertSame($instance1, $instance2, 'Request should be a singleton');
    }

    public function testGetMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('post', $this->request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('get', $this->request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $this->assertEquals('put', $this->request->getMethod());
    }

    public function testGetURI(): void
    {
        $_SERVER['REQUEST_URI'] = '/users/list';
        $this->assertEquals('/users/list', $this->request->getURI());
    }

    public function testGetIP(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';
        $this->assertEquals('192.168.1.1', $this->request->getIP());

        $_SERVER['HTTP_CLIENT_IP'] = '10.0.0.1';
        $this->assertEquals('10.0.0.1', $this->request->getIP());

        unset($_SERVER['HTTP_CLIENT_IP']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '172.16.0.1';
        $this->assertEquals('172.16.0.1', $this->request->getIP());
    }

    public function testIsSecure(): void
    {
        $this->assertFalse($this->request->isSecure());

        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue($this->request->isSecure());

        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse($this->request->isSecure());
    }

    public function testIsXHR(): void
    {
        $this->assertFalse($this->request->isXHR());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue($this->request->isXHR());
    }

    public function testIsAjax(): void
    {
        // Need a fresh instance to test initial state
        unset($GLOBALS['_RPC_']['singleton']['request']);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = null;
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        $request = Request::getInstance();
        $this->assertFalse($request->isAjax());

        // Test with header set
        unset($GLOBALS['_RPC_']['singleton']['request']);
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = Request::getInstance();
        $this->assertTrue($request->isAjax());
    }

    public function testGetQueryString(): void
    {
        $_SERVER['QUERY_STRING'] = 'id=123&name=test';
        $this->assertEquals('id=123&name=test', $this->request->getQueryString());
    }

    public function testGetServerName(): void
    {
        $_SERVER['SERVER_NAME'] = 'example.com';
        $this->assertEquals('example.com', $this->request->getServerName());
    }

    public function testGetServerPort(): void
    {
        $_SERVER['SERVER_PORT'] = '443';
        $this->assertEquals('443', $this->request->getServerPort());
    }

    public function testGetServerAddr(): void
    {
        $_SERVER['SERVER_ADDR'] = '192.168.1.100';
        $this->assertEquals('192.168.1.100', $this->request->getServerAddr());
    }

    public function testGetHostName(): void
    {
        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $this->assertEquals('www.example.com', $this->request->getHostName());
    }

    public function testJsonDecoding(): void
    {
        // Note: Testing json() method properly would require mocking php://input
        // which is complex in unit tests. php://input returns empty in CLI context
        // so json_decode returns null. This would be better tested in a feature test.
        $result = $this->request->json();
        // In CLI/test context, php://input is empty, so we get null or empty array
        $this->assertTrue($result === null || is_array($result));
    }

    public function testGetCookie(): void
    {
        $cookie = $this->request->getCookie('test_cookie');
        $this->assertInstanceOf(\RPC\HTTP\Cookie::class, $cookie);
    }

    public function testMethodConstants(): void
    {
        $this->assertEquals('head', Request::METHOD_HEAD);
        $this->assertEquals('get', Request::METHOD_GET);
        $this->assertEquals('post', Request::METHOD_POST);
        $this->assertEquals('put', Request::METHOD_PUT);
    }
}
