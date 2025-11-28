<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// CORS测试路由组
Route::prefix('cors-test')->group(function () {
    // 正常响应测试
    Route::get('/normal', 'CorsTestController@testNormal');
    Route::post('/post', 'CorsTestController@testPost');
    Route::put('/put/{id}', 'CorsTestController@testPut');
    Route::delete('/delete/{id}', 'CorsTestController@testDelete');
    
    // 错误响应测试
    Route::get('/not-found', 'CorsTestController@testNotFound');
    Route::get('/server-error', 'CorsTestController@testServerError');
    
    // 自定义头测试
    Route::get('/custom-headers', 'CorsTestController@testCustomHeaders');
    
    // 认证测试（需要登录）
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user(),
            'message' => 'Authenticated user data'
        ]);
    });
    
    // 预检请求测试
    Route::options('/preflight', 'CorsTestController@testPreflight');
    
    // CORS配置信息
    Route::get('/config', 'CorsTestController@getCorsConfig');
});

// 现有的API路由保持不变
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// 其他现有的API路由
Route::middleware(['api'])->group(function () {
    // 在这里添加现有的API路由
});