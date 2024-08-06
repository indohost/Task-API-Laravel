<?php

namespace App\Exceptions;

use App\Constants\HandlerConstants;
use App\Traits\MakesHttpRequests;
use App\Traits\ResponseTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait, MakesHttpRequests;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return $this->validationFailedResponse(HandlerConstants::VALIDATION_ERRORS, $exception->errors());
        }

        return parent::render($request, $exception);
    }
}
