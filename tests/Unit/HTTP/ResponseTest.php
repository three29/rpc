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

    // Note: Testing methods that call header(), setcookie(), or exit() would require
    // runInSeparateProcess and outputBuffering annotations or custom test doubles.
    // These include: redirect(), setCookie(), unsetCookie(), addHeader(),
    // setStatus(), noCache(), json(), jsonSuccess(), jsonError()
    // Those are better suited for integration/feature tests.
}
