<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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


    public function render($request, Throwable $exception): Response
    {
        if (($exception instanceof AuthorizationException || $exception instanceof UnauthorizedException)
            && $request->expectsJson() === false
        ) {
            // dd($request);
            return Inertia::render('Errors/403')
                ->toResponse($request)
                ->setStatusCode(403);
        } elseif (
            $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
            || $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
        ) {
            // Illuminate\Database\Eloquent\ModelNotFoundException
            // Redirigir o retornar una vista personalizada
            return Inertia::render('Errors/404')
                ->toResponse($request)
                ->setStatusCode(404);
        } elseif ($exception instanceof \Illuminate\Auth\AuthenticationException) {
        } else {
            dd('Other exception: ', $exception);
        }

        return parent::render($request, $exception);
    }
}
