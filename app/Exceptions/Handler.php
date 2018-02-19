<?php

namespace App\Exceptions;

use App\Common\ApiException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ApiException) {
            return $this->jsonResponse([
                'error' => [
                    'code' => $exception->getApiErrorCode(),
                    'message' => $exception->getMessage(),
                ],
            ], $exception->getHttpStatusCode());
        }
        return parent::render($request, $exception);
    }

    private function jsonResponse(array $payload, int $statusCode): JsonResponse
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }
}
