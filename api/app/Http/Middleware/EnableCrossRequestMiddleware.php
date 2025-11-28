<?php
/** +----------------------------------------------------------------------
 * | TFSHOP [ 轻量级易扩展低代码开源商城系统 ]
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020~2023 https://www.dswjcms.com All rights reserved.
 * +----------------------------------------------------------------------
 * | Licensed 未经许可不能去掉TFSHOP相关版权
 * +----------------------------------------------------------------------
 * | Author: Purl <383354826@qq.com>
 * +----------------------------------------------------------------------
 */
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnableCrossRequestMiddleware
{
    /**
     * CORS配置选项
     */
    protected $corsOptions = [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
        'allowed_headers' => [
            'Origin', 'Content-Type', 'Cookie', 'X-CSRF-TOKEN', 'Accept', 
            'Authorization', 'applyid', 'openid', 'apply-secret', 'versionid', 
            'X-XSRF-TOKEN', 'Lang', 'X-Requested-With'
        ],
        'exposed_headers' => ['Authorization', 'authenticated'],
        'max_age' => 86400, // 24小时
        'supports_credentials' => true,
        'allowed_origins_patterns' => []
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 处理预检请求
        if ($request->method() === 'OPTIONS') {
            return $this->handlePreflightRequest($request);
        }

        try {
            // 继续处理请求
            $response = $next($request);
            
            // 为响应添加CORS头
            return $this->addCorsHeaders($request, $response);
            
        } catch (\Exception $e) {
            // 即使在异常情况下也要确保CORS头被添加
            $response = response()->json([
                'status_code' => 500,
                'code' => $e->getCode(),
                'message' => '服务器内部错误',
                'result' => 'error'
            ], 500);
            
            return $this->addCorsHeaders($request, $response);
        }
    }

    /**
     * 处理预检请求
     *
     * @param Request $request
     * @return Response
     */
    protected function handlePreflightRequest(Request $request): Response
    {
        $response = response()->json([], 200);
        return $this->addCorsHeaders($request, $response);
    }

    /**
     * 添加CORS头到响应
     *
     * @param Request $request
     * @param mixed $response
     * @return mixed
     */
    protected function addCorsHeaders(Request $request, $response)
    {
        if (!$response instanceof Response) {
            return $response;
        }

        $origin = $request->header('Origin', '');
        
        // 验证来源
        if (!$this->isOriginAllowed($origin)) {
            return $response;
        }

        // 设置允许的来源
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        
        // 设置允许的请求方法
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->corsOptions['allowed_methods']));
        
        // 设置允许的请求头
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->corsOptions['allowed_headers']));
        
        // 设置暴露的响应头
        if (!empty($this->corsOptions['exposed_headers'])) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->corsOptions['exposed_headers']));
        }
        
        // 设置是否支持凭据
        if ($this->corsOptions['supports_credentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        
        // 设置预检请求缓存时间
        if ($this->corsOptions['max_age'] > 0) {
            $response->headers->set('Access-Control-Max-Age', (string)$this->corsOptions['max_age']);
        }

        return $response;
    }

    /**
     * 验证来源是否允许
     *
     * @param string $origin
     * @return bool
     */
    protected function isOriginAllowed(string $origin): bool
    {
        // 如果允许所有来源
        if (in_array('*', $this->corsOptions['allowed_origins'])) {
            return true;
        }

        // 检查是否在允许的来源列表中
        if (in_array($origin, $this->corsOptions['allowed_origins'])) {
            return true;
        }

        // 检查是否匹配允许的来源模式
        foreach ($this->corsOptions['allowed_origins_patterns'] as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        return false;
    }
}
