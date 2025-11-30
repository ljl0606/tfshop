<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Fruitcake\Cors\HandleCors; // 引入 CORS 处理器

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
     * @param \Throwable $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return \Illuminate\Http\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        $error = $this->convertExceptionToResponse($exception);
        $responseData['status_code'] = $error->getStatusCode();
        $responseData['code'] = $exception->getCode();
        $responseData['message'] = empty($exception->getMessage()) ? 'something error' : $exception->getMessage();
        if(config('app.debug')) {
            if($error->getStatusCode() >= 500) {
                $responseData['trace'] = $exception->getTraceAsString();
            }
        }
        $responseData['result'] = 'error';
        $response = response()->json($responseData, $error->getStatusCode());
        
        // 如果是跨域请求，则添加 CORS 头
        $cors = app(HandleCors::class);
        $cors->addCorsHeaders($request, $response);
        
        return $response;
    }
}
