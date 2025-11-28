<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * 认证测试路由 - 专门测试认证失败时的CORS处理
 */

// 测试认证失败的路由
Route::prefix('auth-test')->group(function () {
    
    // 需要认证的基础路由
    Route::middleware('auth:api')->get('/protected', function (Request $request) {
        return response()->json([
            'message' => 'This is protected data',
            'user' => $request->user()
        ]);
    });
    
    // 管理员认证路由
    Route::middleware('auth:api')->prefix('admin')->group(function () {
        Route::get('/dashboard', function (Request $request) {
            return response()->json([
                'message' => 'Admin dashboard',
                'user' => $request->user()
            ]);
        });
        
        Route::post('/users', function (Request $request) {
            return response()->json([
                'message' => 'User created',
                'data' => $request->all()
            ]);
        });
    });
    
    // 测试不同HTTP方法的认证
    Route::middleware('auth:api')->match(['GET', 'POST', 'PUT', 'DELETE'], '/multi-method', function (Request $request) {
        return response()->json([
            'message' => 'Authenticated ' . $request->method() . ' request',
            'user' => $request->user()
        ]);
    });
    
});