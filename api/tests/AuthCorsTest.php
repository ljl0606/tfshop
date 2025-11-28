<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

/**
 * CORS配置测试类 - 专门测试认证失败时的CORS处理
 */
class AuthCorsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * 测试未认证的API请求返回正确的CORS头
     */
    public function testUnauthenticatedApiRequestReturnsCorrectCorsHeaders()
    {
        $origin = 'https://example.com';
        
        // 发送一个需要认证的API请求，但不提供认证信息
        $response = $this->withHeaders([
            'Origin' => $origin,
            'Accept' => 'application/json',
        ])->get('/api/admin/user'); // 假设这是需要认证的管理员接口
        
        // 验证响应状态码为401
        $response->assertStatus(401);
        
        // 验证CORS头正确设置
        $response->assertHeader('Access-Control-Allow-Origin', $origin);
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
        $response->assertHeader('Access-Control-Expose-Headers');
        
        // 验证响应内容
        $response->assertJson([
            'status_code' => 401,
            'code' => 401,
            'result' => 'error',
            'message' => 'Unauthenticated: Unauthenticated.'
        ]);
    }

    /**
     * 测试带有无效Token的API请求返回正确的CORS头
     */
    public function testInvalidTokenApiRequestReturnsCorrectCorsHeaders()
    {
        $origin = 'https://example.com';
        
        // 发送一个带有无效Token的API请求
        $response = $this->withHeaders([
            'Origin' => $origin,
            'Accept' => 'application/json',
            'Authorization' => 'Bearer invalid_token_here',
        ])->get('/api/admin/user');
        
        // 验证响应状态码为401
        $response->assertStatus(401);
        
        // 验证CORS头正确设置
        $response->assertHeader('Access-Control-Allow-Origin', $origin);
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        
        // 验证响应内容
        $response->assertJsonStructure([
            'status_code',
            'code',
            'result',
            'message'
        ]);
    }

    /**
     * 测试预检请求在认证失败时的CORS处理
     */
    public function testPreflightRequestWithAuthFailureReturnsCorrectCorsHeaders()
    {
        $origin = 'https://example.com';
        
        // 发送预检请求到需要认证的端点
        $response = $this->withHeaders([
            'Origin' => $origin,
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/admin/user');
        
        // 验证预检请求成功
        $response->assertStatus(200);
        
        // 验证CORS头正确设置
        $response->assertHeader('Access-Control-Allow-Origin', $origin);
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        $response->assertHeader('Access-Control-Max-Age');
    }

    /**
     * 测试多个来源的认证失败CORS处理
     */
    public function testMultipleOriginsAuthFailureCorsHandling()
    {
        $origins = [
            'https://app.example.com',
            'https://admin.example.com',
            'http://localhost:3000'
        ];
        
        foreach ($origins as $origin) {
            $response = $this->withHeaders([
                'Origin' => $origin,
                'Accept' => 'application/json',
            ])->get('/api/admin/user');
            
            // 验证响应状态码为401
            $response->assertStatus(401);
            
            // 验证CORS头正确设置
            $response->assertHeader('Access-Control-Allow-Origin', $origin);
            $response->assertHeader('Vary', 'Origin');
        }
    }

    /**
     * 测试认证失败的POST请求CORS处理
     */
    public function testPostRequestAuthFailureCorsHandling()
    {
        $origin = 'https://example.com';
        
        $response = $this->withHeaders([
            'Origin' => $origin,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('/api/admin/user', [
            'name' => 'Test User'
        ]);
        
        // 验证响应状态码为401
        $response->assertStatus(401);
        
        // 验证CORS头正确设置
        $response->assertHeader('Access-Control-Allow-Origin', $origin);
        $response->assertHeader('Access-Control-Allow-Methods');
        
        // 验证响应内容
        $response->assertJson([
            'status_code' => 401,
            'code' => 401,
            'result' => 'error'
        ]);
    }
}