<?php

namespace App\Exceptions;

use App\Common\ApiException;
use App\Common\ApiFieldErrorsException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Log\LoggerInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
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
        if ($exception instanceof OAuthServerException) {
            try {
                $logger = $this->container->make(LoggerInterface::class);
            } catch (Exception $e) {
                throw $exception; // throw the original exception
            }

            $logger->error(
                $exception->getMessage(),
                ['exception' => $exception]
            );
        } else {
            parent::report($exception);
        }
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
            $responseData = [
                'message' => $exception->getMessage(),
                'error' => $exception->getApiErrorCode(),
            ];
            if ($exception instanceof ApiFieldErrorsException) {
                // Try to match the format Laravel uses for showing field errors.
                $responseData['errors'] = $exception->getErrors();
            }
            return $this->jsonResponse($responseData, $exception->getHttpStatusCode());
        }
        return parent::render($request, $exception);
    }

    private function jsonResponse(array $payload, int $statusCode): JsonResponse
    {
        $payload = $payload ?: [];

        return response()->json($payload, $statusCode);
    }
}
