<?php

namespace Tests\Unit\HTTP;

use Tests\Unit\UnitTestCase;
use RPC\HTTP\Response;

class ResponseTest extends UnitTestCase
{
    private Response $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->response = Response::getInstance();
    }

    public function testGetInstance(): void
    {
        $instance1 = Response::getInstance();
        $instance2 = Response::getInstance();

        $this->assertSame($instance1, $instance2, 'Response should be a singleton');
    }

    public function testSetBuffer(): void
    {
        $content = 'Test content';
        $result = $this->response->setBuffer($content);

        $this->assertInstanceOf(Response::class, $result, 'setBuffer should return Response instance for chaining');
        $this->assertEquals($content, (string) $this->response);
    }

    public function testAppend(): void
    {
        $this->response->setBuffer('Hello');
        $this->response->append(' World');

        $this->assertEquals('Hello World', (string) $this->response);
    }

    public function testPrepend(): void
    {
        $this->response->setBuffer('World');
        $this->response->prepend('Hello ');

        $this->assertEquals('Hello World', (string) $this->response);
    }

    public function testGetContentLength(): void
    {
        $content = 'Test content';
        $this->response->setBuffer($content);

        $this->assertEquals(strlen($content), $this->response->getContentLength());
    }

    public function testToString(): void
    {
        $content = 'Response content';
        $this->response->setBuffer($content);

        $this->assertEquals($content, (string) $this->response);
    }

    public function testHeaderConstants(): void
    {
        $this->assertEquals('Content-type: image/gif', Response::HEADER_GIF);
        $this->assertEquals('Content-type: image/png', Response::HEADER_PNG);
        $this->assertEquals('Content-type: image/jpeg', Response::HEADER_JPEG);
        $this->assertEquals('HTTP/1.0 404 Not Found', Response::HEADER_NOT_FOUND);
    }

    public function testMethodChaining(): void
    {
        $result = $this->response
            ->setBuffer('Start')
            ->append(' Middle')
            ->prepend('Before ');

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('Before Start Middle', (string) $this->response);
    }

    public function testSetBufferOverwritesExistingContent(): void
    {
        $this->response->setBuffer('First');
        $this->response->setBuffer('Second');

        $this->assertEquals('Second', (string) $this->response);
    }

    public function testAppendWithEmptyBuffer(): void
    {
        $this->response->append('Content');

        $this->assertEquals('Content', (string) $this->response);
    }

    public function testPrependWithEmptyBuffer(): void
    {
        $this->response->prepend('Content');

        $this->assertEquals('Content', (string) $this->response);
    }

    public function testMultipleAppends(): void
    {
        $this->response->setBuffer('Start');
        $this->response->append(' Middle');
        $this->response->append(' End');

        $this->assertEquals('Start Middle End', (string) $this->response);
    }

    public function testMultiplePrepends(): void
    {
        $this->response->setBuffer('End');
        $this->response->prepend('Middle ');
        $this->response->prepend('Start ');

        $this->assertEquals('Start Middle End', (string) $this->response);
    }

    public function testGetContentLengthWithEmptyBuffer(): void
    {
        $this->response->setBuffer('');

        $this->assertEquals(0, $this->response->getContentLength());
    }

    public function testGetContentLengthWithMultibyteCharacters(): void
    {
        $content = 'Hello 世界';
        $this->response->setBuffer($content);

        // strlen counts bytes, not characters
        $this->assertEquals(strlen($content), $this->response->getContentLength());
    }

    public function testToStringWithEmptyBuffer(): void
    {
        $this->response->setBuffer('');

        $this->assertEquals('', (string) $this->response);
    }

    public function testBufferWithSpecialCharacters(): void
    {
        $content = "Line 1\nLine 2\tTabbed\r\nWindows line";
        $this->response->setBuffer($content);

        $this->assertEquals($content, (string) $this->response);
    }

    public function testChainedAppendAndPrepend(): void
    {
        $result = $this->response
            ->setBuffer('2')
            ->prepend('1')
            ->append('3')
            ->prepend('0')
            ->append('4');

        $this->assertEquals('01234', (string) $this->response);
    }

    public function testBufferWithHtmlContent(): void
    {
        $html = '<html><body><h1>Test</h1></body></html>';
        $this->response->setBuffer($html);

        $this->assertEquals($html, (string) $this->response);
        $this->assertEquals(strlen($html), $this->response->getContentLength());
    }

    public function testBufferWithJsonContent(): void
    {
        $json = '{"key": "value", "number": 123}';
        $this->response->setBuffer($json);

        $this->assertEquals($json, (string) $this->response);
    }

    public function testNewInstanceIsNotSingleton(): void
    {
        $instance1 = Response::getInstance();
        $instance2 = new Response();

        $this->assertNotSame($instance1, $instance2);
    }

    // Note: Testing methods that call header(), setcookie(), or exit() would require
    // runInSeparateProcess and outputBuffering annotations or custom test doubles.
    // These include: redirect(), setCookie(), unsetCookie(), addHeader(),
    // setStatus(), noCache(), json(), jsonSuccess(), jsonError()
    // Those are better suited for integration/feature tests.
}
