<?php
declare(strict_types=1);

namespace App\Common;

class JsonResponder
{
    public static function respond($data, callable $transformer): array
    {
        $return = ['data' => $transformer($data)];
        return $return;
    }
}
