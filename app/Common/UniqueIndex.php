<?php
declare(strict_types=1);

namespace App\Common;

use Illuminate\Database\QueryException;

class UniqueIndex
{
    public static function isUniqueIndexException(QueryException $exception): bool
    {
        return $exception->getCode() == 23000 &&
            str_contains($exception->getPrevious()->getMessage(), 'UNIQUE constraint failed:');
    }
}
