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

    public function testNewInstanceIsNotSingleton(): void
    {
        $instance1 = Request::getInstance();
        $instance2 = new Request();

        $this->assertNotSame($instance1, $instance2);
    }

    public function testGetIPPriorityOrder(): void
    {
        // HTTP_CLIENT_IP has highest priority
        $_SERVER['HTTP_CLIENT_IP'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '172.16.0.1';
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $this->assertEquals('10.0.0.1', $this->request->getIP());

        // HTTP_X_FORWARDED_FOR is second priority
        unset($_SERVER['HTTP_CLIENT_IP']);
        $this->assertEquals('172.16.0.1', $this->request->getIP());

        // REMOTE_ADDR is lowest priority
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $this->assertEquals('192.168.1.1', $this->request->getIP());
    }

    public function testGetIPReturnsNullWhenNotSet(): void
    {
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);

        $this->assertNull($this->request->getIP());
    }

    public function testGetMethodIsCaseInsensitive(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('get', $this->request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'get';
        $this->assertEquals('get', $this->request->getMethod());

        $_SERVER['REQUEST_METHOD'] = 'Post';
        $this->assertEquals('post', $this->request->getMethod());
    }

    public function testIsSecureWithDifferentValues(): void
    {
        $_SERVER['HTTPS'] = 'ON';
        $this->assertTrue($this->request->isSecure());

        $_SERVER['HTTPS'] = 'On';
        $this->assertTrue($this->request->isSecure());

        $_SERVER['HTTPS'] = 'OFF';
        $this->assertFalse($this->request->isSecure());

        $_SERVER['HTTPS'] = '1';
        $this->assertFalse($this->request->isSecure());

        unset($_SERVER['HTTPS']);
        $this->assertFalse($this->request->isSecure());
    }

    public function testGetQueryStringWithEmptyValue(): void
    {
        $_SERVER['QUERY_STRING'] = '';
        $this->assertEquals('', $this->request->getQueryString());
    }

    public function testGetPathInfoReturnsNullWhenNotSet(): void
    {
        unset($_SERVER['PATH_INFO']);
        $this->assertNull($this->request->getPathInfo());
    }

    public function testGetPathInfoWhenSet(): void
    {
        $_SERVER['PATH_INFO'] = '/users/123';
        $this->assertEquals('/users/123', $this->request->getPathInfo());
    }

    public function testConstructorPopulatesPostGetFiles(): void
    {
        $_POST = ['key1' => 'value1'];
        $_GET = ['key2' => 'value2'];
        $_FILES = ['file1' => ['name' => 'test.txt']];

        $request = new Request();

        $this->assertEquals(['key1' => 'value1'], $request->post);
        $this->assertEquals(['key2' => 'value2'], $request->get);
        $this->assertEquals(['file1' => ['name' => 'test.txt']], $request->files);
    }

    public function testGetUriWithQueryString(): void
    {
        $_SERVER['REQUEST_URI'] = '/users/list?page=1&sort=name';
        $this->assertEquals('/users/list?page=1&sort=name', $this->request->getURI());
    }

    public function testGetUriWithFragment(): void
    {
        $_SERVER['REQUEST_URI'] = '/products#featured';
        $this->assertEquals('/products#featured', $this->request->getURI());
    }

    public function testGetCookieReturnsNewInstanceEachTime(): void
    {
        $cookie1 = $this->request->getCookie('test');
        $cookie2 = $this->request->getCookie('test');

        $this->assertNotSame($cookie1, $cookie2);
        $this->assertEquals($cookie1->getName(), $cookie2->getName());
    }

    public function testSetRouterReturnsRequestInstance(): void
    {
        $router = new \RPC\Router();
        $result = $this->request->setRouter($router);

        $this->assertInstanceOf(Request::class, $result);
        $this->assertSame($this->request, $result);
    }
}
