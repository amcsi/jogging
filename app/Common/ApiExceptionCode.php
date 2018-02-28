<?php
declare(strict_types=1);

namespace App\Common;

class ApiExceptionCode
{
    public const EMAIL_ALREADY_EXISTS = 'EMAIL_ALREADY_EXISTS';
    public const EMAIL_NOT_FOUND = 'email_not_found';
    public const RUNTIME_ERRORS = 'RUNTIME_ERRORS';
}
