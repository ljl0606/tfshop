<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

/**
 * CORS配置测试类
 * 用于验证各种场景下的CORS响应头配置
 */
class CorsTest extends TestCase
{
    /**
     * 测试正常的GET请求CORS头
     */
    public function testNormalGetRequestCorsHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'GET',
        ])->get('/api/test');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * 测试预检请求(OPTIONS)处理
     */
    public function testPreflightRequest()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/test');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        $response->assertHeader('Access-Control-Max-Age', '86400');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * 测试404错误响应的CORS头
     */
    public function test404ErrorCorsHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->get('/api/nonexistent-endpoint');

        $response->assertStatus(404);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * 测试500错误响应的CORS头
     */
    public function test500ErrorCorsHeaders()
    {
        // 创建一个会触发500错误的端点
        \Route::get('/api/test-500', function () {
            throw new \Exception('Test 500 error');
        });

        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->get('/api/test-500');

        $response->assertStatus(500);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * 测试多个不同源的请求
     */
    public function testMultipleOrigins()
    {
        $origins = [
            'http://localhost:3000',
            'http://localhost:8080',
            'https://example.com',
        ];

        foreach ($origins as $origin) {
            $response = $this->withHeaders([
                'Origin' => $origin,
            ])->get('/api/test');

            $response->assertHeader('Access-Control-Allow-Origin', $origin);
        }
    }

    /**
     * 测试无Origin头的请求（不应添加CORS头）
     */
    public function testRequestWithoutOrigin()
    {
        $response = $this->get('/api/test');

        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    /**
     * 测试POST请求带JSON数据的CORS头
     */
    public function testPostRequestWithJsonCorsHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer test-token',
        ])->post('/api/test', [
            'data' => 'test data'
        ]);

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
        $response->assertHeader('Access-Control-Expose-Headers', 'Authorization, X-Custom-Header');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    /**
     * 测试PUT请求方法
     */
    public function testPutRequestCorsHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Content-Type' => 'application/json',
        ])->put('/api/test/1', [
            'name' => 'updated name'
        ]);

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    /**
     * 测试DELETE请求方法
     */
    public function testDeleteRequestCorsHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->delete('/api/test/1');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    /**
     * 测试自定义请求头的CORS支持
     */
    public function testCustomHeadersCorsSupport()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'X-Custom-Header' => 'custom-value',
            'X-API-Key' => 'test-key',
        ])->get('/api/test');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->assertHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN');
    }

    /**
     * 测试预检请求缓存
     */
    public function testPreflightCacheHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
        ])->options('/api/test');

        $response->assertHeader('Access-Control-Max-Age', '86400');
    }

    /**
     * 测试错误响应中的暴露头信息
     */
    public function testErrorResponseExposedHeaders()
    {
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
        ])->get('/api/nonexistent');

        $response->assertHeader('Access-Control-Expose-Headers', 'Authorization, X-Custom-Header');
    }
}