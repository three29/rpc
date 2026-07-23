<?php

namespace Tests\Unit\Db;

use RPC\Db\Statement;
use RPC\Db\Adapter\MySQL;
use Tests\Unit\UnitTestCase;
use PDO;

class StatementTest extends UnitTestCase
{
    private $mockPDO;
    private $mockPDOStatement;
    private $mockAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock PDOStatement
        $this->mockPDOStatement = $this->createMock(\PDOStatement::class);

        // Create mock PDO
        $this->mockPDO = $this->createMock(PDO::class);
        $this->mockPDO->method('prepare')
            ->willReturn($this->mockPDOStatement);

        // Create mock adapter
        $this->mockAdapter = $this->createMock(MySQL::class);
        $this->mockAdapter->method('getHandle')
            ->willReturn($this->mockPDO);
        $this->mockAdapter->method('getFetchMode')
            ->willReturn(\RPC\Db::FETCH_ASSOC);
    }

    public function testStatementConstruction()
    {
        $this->mockPDOStatement->expects($this->once())
            ->method('setFetchMode')
            ->with(\RPC\Db::FETCH_ASSOC);

        $stmt = new Statement('SELECT * FROM users', $this->mockAdapter);

        $this->assertInstanceOf(Statement::class, $stmt);
    }

    public function testSetFetchMode()
    {
        $this->mockPDOStatement->expects($this->exactly(2))
            ->method('setFetchMode');

        $stmt = new Statement('SELECT * FROM users', $this->mockAdapter);
        $result = $stmt->setFetchMode(\RPC\Db::FETCH_NUM);

        $this->assertSame($stmt, $result); // Fluent interface
    }

    public function testExecuteWithSelectQuery()
    {
        $expectedRows = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];

        $this->mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with([])
            ->willReturn(true);

        $this->mockPDOStatement->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedRows);

        $this->mockPDOStatement->expects($this->once())
            ->method('closeCursor');

        $stmt = new Statement('SELECT * FROM users', $this->mockAdapter);
        $result = $stmt->execute([]);

        $this->assertEquals($expectedRows, $result);
    }

    public function testExecuteWithInsertQuery()
    {
        $this->mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with(['John'])
            ->willReturn(true);

        $stmt = new Statement('INSERT INTO users (name) VALUES (?)', $this->mockAdapter);
        $result = $stmt->execute(['John']);

        $this->assertTrue($result);
    }

    public function testExecuteWithUpdateQuery()
    {
        $this->mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with(['Jane', 1])
            ->willReturn(true);

        $stmt = new Statement('UPDATE users SET name = ? WHERE id = ?', $this->mockAdapter);
        $result = $stmt->execute(['Jane', 1]);

        $this->assertTrue($result);
    }

    public function testExecuteWithDeleteQuery()
    {
        $this->mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $stmt = new Statement('DELETE FROM users WHERE id = ?', $this->mockAdapter);
        $result = $stmt->execute([1]);

        $this->assertTrue($result);
    }

    public function testExecuteFailure()
    {
        $this->mockPDOStatement->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        $stmt = new Statement('SELECT * FROM users', $this->mockAdapter);
        $result = $stmt->execute([]);

        $this->assertFalse($result);
    }

    public function testExecuteWithNamedParameters()
    {
        $params = [':name' => 'John', ':email' => 'john@example.com'];

        $this->mockPDOStatement->expects($this->once())
            ->method('execute')
            ->with($params)
            ->willReturn(true);

        $stmt = new Statement('INSERT INTO users (name, email) VALUES (:name, :email)', $this->mockAdapter);
        $result = $stmt->execute($params);

        $this->assertTrue($result);
    }

    public function testBuildDebugSqlWithNullParameter()
    {
        $stmt = new Statement('SELECT * FROM users WHERE deleted_at = ?', $this->mockAdapter);

        $reflection = new \ReflectionClass($stmt);
        $method = $reflection->getMethod('buildDebugSql');

        $debugSql = $method->invoke($stmt, 'SELECT * FROM users WHERE deleted_at = ?', [null]);

        $this->assertStringContainsString('NULL', $debugSql);
    }

    public function testBuildDebugSqlWithBooleanParameter()
    {
        $stmt = new Statement('SELECT * FROM users WHERE active = ?', $this->mockAdapter);

        $reflection = new \ReflectionClass($stmt);
        $method = $reflection->getMethod('buildDebugSql');

        $debugSql = $method->invoke($stmt, 'SELECT * FROM users WHERE active = ?', [true]);

        $this->assertStringContainsString('1', $debugSql);
    }

    public function testBuildDebugSqlWithNumericParameter()
    {
        $stmt = new Statement('SELECT * FROM users WHERE id = ?', $this->mockAdapter);

        $reflection = new \ReflectionClass($stmt);
        $method = $reflection->getMethod('buildDebugSql');

        $debugSql = $method->invoke($stmt, 'SELECT * FROM users WHERE id = ?', [42]);

        $this->assertStringContainsString('42', $debugSql);
    }
}
