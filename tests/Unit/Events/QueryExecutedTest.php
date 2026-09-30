<?php

namespace Tests\Unit\Events;

use RPC\Events\QueryExecuted;
use Tests\Unit\UnitTestCase;

class QueryExecutedTest extends UnitTestCase
{
    public function testEventConstruction()
    {
        $event = new QueryExecuted('SELECT * FROM users', 'select');

        $this->assertInstanceOf(QueryExecuted::class, $event);
    }

    public function testEventContainsSql()
    {
        $sql = 'SELECT * FROM users WHERE id = ?';
        $event = new QueryExecuted($sql, 'select');

        $this->assertEquals($sql, $event->sql);
    }

    public function testEventContainsType()
    {
        $event = new QueryExecuted('SELECT * FROM users', 'select');

        $this->assertEquals('select', $event->type);
    }

    public function testReadonlySqlProperty()
    {
        $event = new QueryExecuted('SELECT * FROM users', 'select');

        $this->assertEquals('SELECT * FROM users', $event->sql);
    }

    public function testReadonlyTypeProperty()
    {
        $event = new QueryExecuted('SELECT * FROM users', 'select');

        $this->assertEquals('select', $event->type);
    }

    public function testDifferentQueryTypes()
    {
        $selectEvent = new QueryExecuted('SELECT * FROM users', 'select');
        $insertEvent = new QueryExecuted('INSERT INTO users (name) VALUES (?)', 'insert');
        $updateEvent = new QueryExecuted('UPDATE users SET name = ?', 'update');
        $deleteEvent = new QueryExecuted('DELETE FROM users WHERE id = ?', 'delete');

        $this->assertEquals('select', $selectEvent->type);
        $this->assertEquals('insert', $insertEvent->type);
        $this->assertEquals('update', $updateEvent->type);
        $this->assertEquals('delete', $deleteEvent->type);
    }
}
