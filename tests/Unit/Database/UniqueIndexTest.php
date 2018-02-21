<?php
declare(strict_types=1);

namespace App\Common;

use Illuminate\Database\QueryException;

final class UniqueIndexTest extends \PHPUnit\Framework\TestCase
{
    public function testIsUniqueIndexException(): void
    {
        $sqlMessage = 'SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: users.email';
        $queryException = new QueryException('', [], new \Exception($sqlMessage, 23000));
        $this->assertTrue(UniqueIndex::isUniqueIndexException($queryException));
    }

    public function testIsJustNotNullConstraint(): void
    {
        $sqlMessage = 'SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: users.role';
        $queryException = new QueryException('', [], new \Exception($sqlMessage, 23000));
        $this->assertFalse(UniqueIndex::isUniqueIndexException($queryException));
    }
}
