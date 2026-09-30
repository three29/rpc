<?php

namespace Tests\Unit\Db;

use RPC\Db\Adapter;
use Tests\Unit\UnitTestCase;

/**
 * Nested transactions against a real (in-memory SQLite) connection
 */
class TransactionTest extends UnitTestCase
{
    private SqliteTestAdapter $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new SqliteTestAdapter(new \PDO('sqlite::memory:'));
        $this->db->getHandle()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->getHandle()->exec('CREATE TABLE items (name TEXT NOT NULL)');
    }

    private function names(): array
    {
        return $this->db->getHandle()->query('SELECT name FROM items ORDER BY rowid')->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function insert(string $name): void
    {
        $this->db->getHandle()->prepare('INSERT INTO items (name) VALUES (?)')->execute([$name]);
    }

    public function testSingleTransactionCommits(): void
    {
        $this->db->beginTransaction();
        $this->insert('a');
        $this->db->commit();

        $this->assertSame(['a'], $this->names());
        $this->assertFalse($this->db->inTransaction());
    }

    public function testNestedBeginDoesNotThrowAndOnlyOuterCommitCommits(): void
    {
        $this->db->beginTransaction();
        $this->insert('outer');

        $this->db->beginTransaction();
        $this->insert('inner');
        $this->assertSame(2, $this->db->getTransactionLevel());
        $this->db->commit();

        $this->assertTrue($this->db->getHandle()->inTransaction());
        $this->db->commit();

        $this->assertFalse($this->db->getHandle()->inTransaction());
        $this->assertSame(['outer', 'inner'], $this->names());
    }

    public function testInnerRollbackOnlyUndoesInnerWork(): void
    {
        $this->db->beginTransaction();
        $this->insert('outer');

        $this->db->beginTransaction();
        $this->insert('inner');
        $this->db->rollback();

        $this->db->commit();

        $this->assertSame(['outer'], $this->names());
    }

    public function testOuterRollbackUndoesCommittedInnerWork(): void
    {
        $this->db->beginTransaction();
        $this->db->beginTransaction();
        $this->insert('inner');
        $this->db->commit();
        $this->db->rollback();

        $this->assertSame([], $this->names());
        $this->assertSame(0, $this->db->getTransactionLevel());
    }

    public function testTransactionHelperCommitsAndReturnsValue(): void
    {
        $result = $this->db->transaction(function (Adapter $db) {
            $this->insert('a');
            return 'done';
        });

        $this->assertSame('done', $result);
        $this->assertSame(['a'], $this->names());
    }

    public function testTransactionHelperRollsBackAndRethrows(): void
    {
        try {
            $this->db->transaction(function () {
                $this->insert('a');
                $this->db->transaction(function () {
                    $this->insert('b');
                });
                throw new \RuntimeException('fail after nested work');
            });
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertSame('fail after nested work', $e->getMessage());
        }

        $this->assertSame([], $this->names());
        $this->assertFalse($this->db->inTransaction());
    }

    public function testNestedHelperFailureCanBeCaughtByOuter(): void
    {
        $this->db->transaction(function () {
            $this->insert('kept');
            try {
                $this->db->transaction(function () {
                    $this->insert('discarded');
                    throw new \RuntimeException('inner');
                });
            } catch (\RuntimeException $e) {
            }
        });

        $this->assertSame(['kept'], $this->names());
    }

    public function testDepthIsSharedByAdaptersOnTheSameConnection(): void
    {
        $other = new SqliteTestAdapter($this->db->getHandle());

        $this->db->beginTransaction();
        $other->beginTransaction();

        $this->assertSame(2, $this->db->getTransactionLevel());
        $other->commit();
        $this->db->commit();

        $this->assertFalse($this->db->getHandle()->inTransaction());
    }

    public function testDepthResetsWhenTransactionEndedOutsideAdapter(): void
    {
        $this->db->beginTransaction();
        $this->db->getHandle()->commit();

        $this->assertSame(0, $this->db->getTransactionLevel());

        // A fresh transaction works normally
        $this->db->beginTransaction();
        $this->insert('a');
        $this->db->commit();
        $this->assertSame(['a'], $this->names());
    }
}

class SqliteTestAdapter extends Adapter
{
    public function __construct(\PDO $pdo)
    {
        $this->setHandle($pdo);
    }

    public function connect(string $username, string $password, mixed $options = null): mixed
    {
        return $this;
    }

    public function getLastId(): mixed
    {
        return $this->getHandle()->lastInsertId();
    }

    public function setCharset(?string $charset = null): mixed
    {
        return true;
    }

    public function prepare(string $sql, mixed $options = null): \RPC\Db\Statement
    {
        throw new \LogicException('not used');
    }
}
