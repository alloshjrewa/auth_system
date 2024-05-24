<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ResponseTrait;

use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ResponseTrait;
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
        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof NotFoundHttpException) {
                return $this->NotFoundException($e);
            }
            if (!$request->is('api/auth/*')) {
                return $this->ApiAuthException($e);
            }

            if ($e instanceof ModelNotFoundException) {
                return $this->RecordNotFoundException($e);
            }
            if ($e instanceof ValidationException) {
                return $this->ValidationException($e);
            }
            if ($e instanceof AuthenticationException) {
                return $this->AuthenticationException($e);
            }
            if ($e instanceof ConflictHttpException) {
                return response()->json([
                    'message' => 'Email address already exists.'
                ], Response::HTTP_CONFLICT);
            }
        });
    }
}
