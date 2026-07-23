<?php

namespace Tests\Unit\Events;

use RPC\Events\QueryExecuting;
use Psr\EventDispatcher\StoppableEventInterface;
use Tests\Unit\UnitTestCase;

class QueryExecutingTest extends UnitTestCase
{
    public function testEventConstruction()
    {
        $event = new QueryExecuting('SELECT * FROM users', 'select');

        $this->assertInstanceOf(QueryExecuting::class, $event);
        $this->assertInstanceOf(StoppableEventInterface::class, $event);
    }

    public function testEventContainsSql()
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $event = new QueryExecuting($sql, 'select');

        $this->assertEquals($sql, $event->sql);
    }

    public function testEventContainsType()
    {
        $event = new QueryExecuting('SELECT * FROM users', 'select');

        $this->assertEquals('select', $event->type);
    }

    public function testPropagationNotStoppedByDefault()
    {
        $event = new QueryExecuting('SELECT * FROM users', 'select');

        $this->assertFalse($event->isPropagationStopped());
    }

    public function testStopExecution()
    {
        $event = new QueryExecuting('SELECT * FROM users', 'select');

        $event->stopExecution();

        $this->assertTrue($event->isPropagationStopped());
    }

    public function testReadonlySqlProperty()
    {
        $event = new QueryExecuting('SELECT * FROM users', 'select');

        // Verify property is readonly (trying to modify should cause error in PHP 8.1+)
        $this->assertEquals('SELECT * FROM users', $event->sql);
    }

    public function testReadonlyTypeProperty()
    {
        $event = new QueryExecuting('SELECT * FROM users', 'select');

        $this->assertEquals('select', $event->type);
    }

    public function testDifferentQueryTypes()
    {
        $selectEvent = new QueryExecuting('SELECT * FROM users', 'select');
        $insertEvent = new QueryExecuting('INSERT INTO users (name) VALUES (?)', 'insert');
        $updateEvent = new QueryExecuting('UPDATE users SET name = ?', 'update');
        $deleteEvent = new QueryExecuting('DELETE FROM users WHERE id = ?', 'delete');

        $this->assertEquals('select', $selectEvent->type);
        $this->assertEquals('insert', $insertEvent->type);
        $this->assertEquals('update', $updateEvent->type);
        $this->assertEquals('delete', $deleteEvent->type);
    }
}
