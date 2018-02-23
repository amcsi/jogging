<?php
declare(strict_types=1);

namespace App\Common;

/**
 * An exception that can be thrown that will include 'errors' as part of the response for placing into form fields.
 */
class ApiFieldErrorsException extends ApiException
{
    private $errors;

    /**
     * @param array $errors Fields as the keys, and errors being an array of strings as error messages for the field.
     * @param \Throwable|null $previous
     */
    public function __construct(array $errors, \Throwable $previous = null)
    {
        parent::__construct('Validation error', ApiExceptionCode::RUNTIME_ERRORS, 409, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
