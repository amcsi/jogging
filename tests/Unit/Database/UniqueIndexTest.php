<?php
declare(strict_types=1);

namespace App\Common;

use Illuminate\Database\QueryException;

final class UniqueIndexTest extends \PHPUnit\Framework\TestCase
{
    public function testIsUniqueIndexException(): void
    {
        $queryException = new QueryException('', [], new \Exception('', 23000));
        $this->assertTrue(UniqueIndex::isUniqueIndexException($queryException));
    }
}
