<?php
/**
 * CORS服务类
 * 提供跨域资源共享相关的服务功能
 */

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CorsService
{
    /**
     * CORS配置选项
     */
    protected $config = [
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
        'allowed_headers' => [
            'Origin', 'Content-Type', 'Cookie', 'X-CSRF-TOKEN', 'Accept', 
            'Authorization', 'applyid', 'openid', 'apply-secret', 'versionid', 
            'X-XSRF-TOKEN', 'Lang', 'X-Requested-With', 'X-HTTP-Method-Override'
        ],
        'exposed_headers' => ['Authorization', 'authenticated', 'X-Total-Count'],
        'max_age' => 86400,
        'supports_credentials' => true
    ];

    /**
     * 为响应添加CORS头
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function addCorsHeaders(Request $request, Response $response): Response
    {
        $origin = $request->header('Origin', '');
        
        // 验证来源
        if (!$this->isOriginAllowed($origin)) {
            return $response;
        }

        // 设置允许的来源
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        
        // 设置允许的请求方法
        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $this->config['allowed_methods']));
        
        // 设置允许的请求头
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $this->config['allowed_headers']));
        
        // 设置暴露的响应头
        if (!empty($this->config['exposed_headers'])) {
            $response->headers->set('Access-Control-Expose-Headers', implode(', ', $this->config['exposed_headers']));
        }
        
        // 设置是否支持凭据
        if ($this->config['supports_credentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
        
        // 设置预检请求缓存时间
        if ($this->config['max_age'] > 0) {
            $response->headers->set('Access-Control-Max-Age', (string)$this->config['max_age']);
        }

        return $response;
    }

    /**
     * 处理预检请求
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handlePreflightRequest(Request $request): JsonResponse
    {
        $response = response()->json([], 200);
        return $this->addCorsHeaders($request, $response);
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
        if (in_array('*', $this->config['allowed_origins'])) {
            return true;
        }

        // 检查是否在允许的来源列表中
        if (in_array($origin, $this->config['allowed_origins'])) {
            return true;
        }

        return false;
    }

    /**
     * 创建带有CORS头的错误响应
     *
     * @param Request $request
     * @param int $statusCode
     * @param string $message
     * @param array $additionalData
     * @return JsonResponse
     */
    public function createErrorResponse(Request $request, int $statusCode, string $message, array $additionalData = []): JsonResponse
    {
        $responseData = array_merge([
            'status_code' => $statusCode,
            'message' => $message,
            'result' => 'error',
            'timestamp' => time()
        ], $additionalData);

        $response = response()->json($responseData, $statusCode);
        
        return $this->addCorsHeaders($request, $response);
    }

    /**
     * 获取CORS配置
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * 更新CORS配置
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }
}