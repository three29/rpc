<?php

namespace Tests\Unit\Registry;

use RPC\Registry\NotFoundException;
use Psr\Container\NotFoundExceptionInterface;
use Tests\Unit\UnitTestCase;

class NotFoundExceptionTest extends UnitTestCase
{
    public function testExceptionConstruction()
    {
        $exception = new NotFoundException('Entry not found');

        $this->assertInstanceOf(NotFoundException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Entry not found', $exception->getMessage());
    }

    public function testExceptionImplementsPsr11Interface()
    {
        $exception = new NotFoundException('Test');

        $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }

    public function testExceptionWithCode()
    {
        $exception = new NotFoundException('Not found', 404);

        $this->assertEquals('Not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }

    public function testExceptionWithPrevious()
    {
        $previous = new \Exception('Previous exception');
        $exception = new NotFoundException('Entry not found', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionIsThrowable()
    {
        $this->expectException(NotFoundException::class);
        throw new NotFoundException('Test throw');
    }

    public function testExceptionCatchableAsPsr11Interface()
    {
        try {
            throw new NotFoundException('Test');
        } catch (NotFoundExceptionInterface $e) {
            $this->assertInstanceOf(NotFoundException::class, $e);
            $this->assertEquals('Test', $e->getMessage());
        }
    }
}
