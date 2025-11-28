<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * CORS测试控制器
 * 用于验证CORS配置的正确性
 */
class CorsTestController extends Controller
{
    /**
     * 测试正常响应
     */
    public function testNormal()
    {
        return response()->json([
            'message' => 'CORS test successful',
            'timestamp' => now()->toIso8601String(),
            'headers' => request()->headers->all()
        ]);
    }

    /**
     * 测试POST请求
     */
    public function testPost(Request $request)
    {
        return response()->json([
            'message' => 'POST request successful',
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ]);
    }

    /**
     * 测试PUT请求
     */
    public function testPut(Request $request, $id)
    {
        return response()->json([
            'message' => 'PUT request successful',
            'id' => $id,
            'data' => $request->all()
        ]);
    }

    /**
     * 测试DELETE请求
     */
    public function testDelete($id)
    {
        return response()->json([
            'message' => 'DELETE request successful',
            'id' => $id
        ]);
    }

    /**
     * 测试404错误
     */
    public function testNotFound()
    {
        return response()->json([
            'error' => 'Resource not found'
        ], 404);
    }

    /**
     * 测试500错误
     */
    public function testServerError()
    {
        throw new \Exception('Test server error for CORS validation');
    }

    /**
     * 测试自定义响应头
     */
    public function testCustomHeaders()
    {
        return response()
            ->json([
                'message' => 'Custom headers test',
                'custom_data' => 'test value'
            ])
            ->header('X-Custom-Header', 'custom-value')
            ->header('X-API-Version', '1.0')
            ->header('Authorization', 'Bearer test-token');
    }

    /**
     * 测试需要认证的端点
     */
    public function testAuthenticated()
    {
        return response()->json([
            'message' => 'Authenticated endpoint accessed',
            'user' => auth()->user() ?: 'guest',
            'authenticated' => auth()->check()
        ]);
    }

    /**
     * 测试预检请求
     */
    public function testPreflight()
    {
        // OPTIONS请求会自动由中间件处理
        return response()->json([
            'message' => 'Preflight request handled',
            'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Accept', 'Origin', 'X-CSRF-TOKEN']
        ]);
    }

    /**
     * 获取当前CORS配置信息
     */
    public function getCorsConfig()
    {
        return response()->json([
            'cors_config' => config('cors'),
            'middleware_stack' => app('router')->getMiddlewareGroups(),
            'current_headers' => request()->headers->all()
        ]);
    }
}