<?php

namespace Tests\Unit\Exception;

use RPC\Exception\Exception;
use RPC\Exception\RuntimeException;
use RPC\Exception\ConfigurationException;
use RPC\Exception\DatabaseException;
use RPC\Exception\HttpException;
use RPC\Exception\InvalidArgumentException;
use RPC\Exception\NotFoundException;
use RPC\Exception\NotImplementedException;
use RPC\Exception\RoutingException;
use RPC\Exception\SecurityException;
use RPC\Exception\ValidationException;
use RPC\Exception\ViewException;
use Tests\Unit\UnitTestCase;

class ExceptionTest extends UnitTestCase
{
    // Base Exception tests
    public function testBaseExceptionConstruction()
    {
        $exception = new Exception('Test message');

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testBaseExceptionWithCode()
    {
        $exception = new Exception('Test message', 123);

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(123, $exception->getCode());
    }

    public function testBaseExceptionWithPrevious()
    {
        $previous = new \Exception('Previous exception');
        $exception = new Exception('Test message', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    // RuntimeException tests
    public function testRuntimeExceptionConstruction()
    {
        $exception = new RuntimeException('Runtime error');

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Runtime error', $exception->getMessage());
    }

    public function testRuntimeExceptionWithCode()
    {
        $exception = new RuntimeException('Runtime error', 500);

        $this->assertEquals(500, $exception->getCode());
    }

    // ConfigurationException tests
    public function testConfigurationExceptionConstruction()
    {
        $exception = new ConfigurationException('Invalid configuration');

        $this->assertInstanceOf(ConfigurationException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Invalid configuration', $exception->getMessage());
    }

    public function testConfigurationExceptionInheritance()
    {
        $exception = new ConfigurationException('Config error');

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    // DatabaseException tests
    public function testDatabaseExceptionConstruction()
    {
        $exception = new DatabaseException('Database connection failed');

        $this->assertInstanceOf(DatabaseException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Database connection failed', $exception->getMessage());
    }

    public function testDatabaseExceptionWithSqlError()
    {
        $exception = new DatabaseException('Syntax error in SQL', 1064);

        $this->assertEquals('Syntax error in SQL', $exception->getMessage());
        $this->assertEquals(1064, $exception->getCode());
    }

    // HttpException tests
    public function testHttpExceptionConstruction()
    {
        $exception = new HttpException('HTTP error');

        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testHttpExceptionWithStatusCode()
    {
        $exception = new HttpException('Not found', 404);

        $this->assertEquals('Not found', $exception->getMessage());
        $this->assertEquals(404, $exception->getCode());
    }

    // InvalidArgumentException tests
    public function testInvalidArgumentExceptionConstruction()
    {
        $exception = new InvalidArgumentException('Invalid argument provided');

        $this->assertInstanceOf(InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Invalid argument provided', $exception->getMessage());
    }

    // NotFoundException tests
    public function testNotFoundExceptionConstruction()
    {
        $exception = new NotFoundException('Resource not found');

        $this->assertInstanceOf(NotFoundException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Resource not found', $exception->getMessage());
    }

    public function testNotFoundExceptionWith404()
    {
        $exception = new NotFoundException('Page not found', 404);

        $this->assertEquals(404, $exception->getCode());
    }

    // NotImplementedException tests
    public function testNotImplementedExceptionConstruction()
    {
        $exception = new NotImplementedException('Feature not implemented');

        $this->assertInstanceOf(NotImplementedException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Feature not implemented', $exception->getMessage());
    }

    // RoutingException tests
    public function testRoutingExceptionConstruction()
    {
        $exception = new RoutingException('Route not found');

        $this->assertInstanceOf(RoutingException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Route not found', $exception->getMessage());
    }

    // SecurityException tests
    public function testSecurityExceptionConstruction()
    {
        $exception = new SecurityException('Access denied');

        $this->assertInstanceOf(SecurityException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Access denied', $exception->getMessage());
    }

    public function testSecurityExceptionWith403()
    {
        $exception = new SecurityException('Forbidden', 403);

        $this->assertEquals(403, $exception->getCode());
    }

    // ValidationException tests
    public function testValidationExceptionConstruction()
    {
        $exception = new ValidationException('Validation failed');

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertEquals('Validation failed', $exception->getMessage());
    }

    // ViewException tests
    public function testViewExceptionConstruction()
    {
        $exception = new ViewException('Template not found');

        $this->assertInstanceOf(ViewException::class, $exception);
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Template not found', $exception->getMessage());
    }

    // Test exception hierarchy
    public function testAllExceptionsExtendBaseException()
    {
        $exceptions = [
            new RuntimeException('test'),
            new ConfigurationException('test'),
            new DatabaseException('test'),
            new HttpException('test'),
            new InvalidArgumentException('test'),
            new NotFoundException('test'),
            new NotImplementedException('test'),
            new RoutingException('test'),
            new SecurityException('test'),
            new ValidationException('test'),
            new ViewException('test'),
        ];

        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(Exception::class, $exception);
            $this->assertInstanceOf(\Exception::class, $exception);
        }
    }

    // Test throwable
    public function testExceptionsAreThrowable()
    {
        $this->expectException(ConfigurationException::class);
        throw new ConfigurationException('Test throw');
    }

    public function testExceptionsCatchableAsBaseException()
    {
        try {
            throw new DatabaseException('Test');
        } catch (Exception $e) {
            $this->assertInstanceOf(DatabaseException::class, $e);
            $this->assertEquals('Test', $e->getMessage());
        }
    }

    public function testExceptionsCatchableAsRuntimeException()
    {
        try {
            throw new DatabaseException('Test');
        } catch (RuntimeException $e) {
            $this->assertInstanceOf(DatabaseException::class, $e);
        }
    }
}
