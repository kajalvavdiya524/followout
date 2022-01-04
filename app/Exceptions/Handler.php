<?php

namespace App\Exceptions;

use App\Followout;
use App\User;
use Exception;
use FollowoutHelper;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \MongoDB\Driver\Exception\ConnectionTimeoutException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated: ' . $exception->getMessage(),
                ], 401);
            }
        }

        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return response()->view('errors.csrf', [], 419);
        }

        if ($exception instanceof \MongoDB\Driver\Exception\ConnectionTimeoutException) {
            return response()->view('errors.db', [], 503);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException ||
            $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            // Same as in App\HomeController@welcome
            $data['exception'] = $exception;
            $data['followouts'] = Followout::notGeoCoupon()->ongoingOrUpcoming()->orderBy('starts_at')->take(100)->get();
            $data['followouts'] = FollowoutHelper::filterFollowoutsForUser($data['followouts'], auth()->user());
            $data['geo_coupon_followouts'] = Followout::geoCoupon()->ongoingOrUpcoming()->orderBy('starts_at')->take(100)->get();
            $data['geo_coupon_followouts'] = FollowoutHelper::filterFollowoutsForUser($data['geo_coupon_followouts'], auth()->user());
            $data['followees'] = User::activated()->public()->followees()->orderBy('created_at', 'DESC')->take(25)->get();
            $data['followhosts'] = User::activated()->public()->followhosts()->subscribed()->orderBy('created_at', 'DESC')->take(25)->get();

            return response()->view('errors.404', compact('data'), 404);
        }

        // Load generic error page with message
        if (!config('app.debug')) {
            if (!$exception instanceof \Illuminate\Validation\ValidationException && !$exception instanceof \Illuminate\Auth\AuthenticationException) {
                $data['exception'] = $exception;

                return response()->view('errors.500', compact('data'), 500);
            }
        }

        return parent::render($request, $exception);
    }
}
