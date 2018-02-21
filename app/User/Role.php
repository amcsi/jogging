<?php
declare(strict_types=1);

namespace App\User;

class Role
{
    public const USER = 'user';
    public const MANAGER = 'manager';
    public const ADMIN = 'admin';

    public static function getAll(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }
}
