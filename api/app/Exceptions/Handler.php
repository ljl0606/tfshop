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
        $statusCode = $error->getStatusCode();
        
        // 构建错误响应
        $response = [
            'status_code' => $statusCode,
            'code' => $exception->getCode(),
            'message' => empty($exception->getMessage()) ? 'something error' : $exception->getMessage(),
            'result' => 'error'
        ];
        
        // 调试模式下添加详细信息
        if (config('app.debug')) {
            if ($statusCode >= 500) {
                $response['trace'] = $exception->getTraceAsString();
                $response['file'] = $exception->getFile();
                $response['line'] = $exception->getLine();
            }
        }
        
        // 创建JSON响应
        $jsonResponse = response()->json($response, $statusCode);
        
        // 为错误响应添加CORS头，确保跨域请求在错误情况下也能正常处理
        if ($request->header('Origin')) {
            $this->addCorsHeadersToResponse($request, $jsonResponse);
        }
        
        return $jsonResponse;
    }
    
    /**
     * 为响应添加CORS头
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\JsonResponse $response
     * @return \Illuminate\Http\JsonResponse
     */
    protected function addCorsHeadersToResponse($request, $response)
    {
        $origin = $request->header('Origin', '');
        
        // 设置CORS头
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
        $response->headers->set('Access-Control-Allow-Headers', 
            'Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, ' .
            'applyid, openid, apply-secret, versionid, X-XSRF-TOKEN, Lang, X-Requested-With'
        );
        $response->headers->set('Access-Control-Expose-Headers', 'Authorization, authenticated');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24小时
        
        return $response;
    }
}
