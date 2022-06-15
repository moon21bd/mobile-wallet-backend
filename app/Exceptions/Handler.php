<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use GuzzleHttp\Exception\RequestException as GuzzleHttpRequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Request;

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
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
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
        /*if ($this->isHttpException($exception) && Request::is('api*') ) {
            switch ($exception->getStatusCode()) {

                // Forbidden
                case '403':
                    return response()->json([
                        'code' => 403, 'message' => 'Forbidden',
                    ], 403);

                // not found
                case '404':
                    return response()->json([
                        'code' => 404, 'message' => 'Not Found',
                    ], 404);

                // Method not allowed
                case '405':
                    return response()->json([
                        'code' => 405, 'message' => $exception->getMessage(),
                    ], 405);

                case '429':
                    return response()->json([
                        'code' => 429, 'message' => $exception->getMessage(),
                    ], 429);

                // internal error
                case '500':
                    return response()->json([
                        'code' => 500, 'message' => $exception->getMessage(),
                    ], 500);

                default:
                    return $this->renderHttpException($exception);
            }
        } else {
            if ($exception instanceof ModelNotFoundException && Request::is('api*')) {
                return response()->json([
                    'code' => 404,
                    'message' => 'Entry for '.str_replace('App\\', '', $exception->getModel()).' not found',
                ], 404);
            } else if ($exception instanceof RequestException || $exception instanceof GuzzleHttpRequestException) {
                return response()->json([
                    'code' => 500,
                    'message' => $exception->getMessage(),
                ], 500);
            } else if ($exception instanceof \BadMethodCallException
                || $exception instanceof \ErrorException
                || $exception instanceof QueryException
                || $exception instanceof RelationNotFoundException
                || $exception instanceof \TypeError) {
                return response()->json([
                    'code' => 500,
                    'message' => $exception->getMessage(),
                ], 500);
            }
        }*/

        return parent::render($request, $exception);
    }
}
