<?php

namespace Tests\Unit\Registry;

use RPC\Registry\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Tests\Unit\UnitTestCase;

class ContainerExceptionTest extends UnitTestCase
{
    public function testExceptionConstruction()
    {
        $exception = new ContainerException('Container error');

        $this->assertInstanceOf(ContainerException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Container error', $exception->getMessage());
    }

    public function testExceptionImplementsPsr11Interface()
    {
        $exception = new ContainerException('Test');

        $this->assertInstanceOf(ContainerExceptionInterface::class, $exception);
    }

    public function testExceptionWithCode()
    {
        $exception = new ContainerException('Error', 500);

        $this->assertEquals('Error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testExceptionWithPrevious()
    {
        $previous = new \Exception('Previous exception');
        $exception = new ContainerException('Container error', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionIsThrowable()
    {
        $this->expectException(ContainerException::class);
        throw new ContainerException('Test throw');
    }

    public function testExceptionCatchableAsPsr11Interface()
    {
        try {
            throw new ContainerException('Test');
        } catch (ContainerExceptionInterface $e) {
            $this->assertInstanceOf(ContainerException::class, $e);
            $this->assertEquals('Test', $e->getMessage());
        }
    }
}
