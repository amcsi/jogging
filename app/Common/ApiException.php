<?php
declare(strict_types=1);

namespace App\Common;

class ApiException extends \Exception
{
    private $apiErrorCode;
    private $httpStatusCode;

    public function __construct(string $message, string $apiErrorCode, int $httpStatusCode, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->apiErrorCode = $apiErrorCode;
        $this->httpStatusCode = $httpStatusCode;
    }

    public function getApiErrorCode(): string
    {
        return $this->apiErrorCode;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
