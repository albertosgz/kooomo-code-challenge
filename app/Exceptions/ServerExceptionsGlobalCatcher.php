<?php

namespace App\Exceptions;

use App\Exceptions\PaymentFailedException;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class ServerExceptionsGlobalCatcher
{

    /**
     * Handle the exception.
     *
     * @param \Throwable $ex
     * @param \Closure $next
     * @return ErrorResponse
     */
    public function handle(\Throwable $ex, \Closure $next): ErrorResponse
    {
        if (!$ex->getCode() || $ex->getCode() >= 500) {
            return ErrorResponse::error([
                'code' => $ex->getCode(),
                'detail' => $ex->getMessage(),
                'status' => '400',
                'title' => 'Unexpected error',
            ]);
        }
        return $next($ex);
    }
}
