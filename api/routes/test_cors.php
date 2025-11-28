<?php

/**
 * CORS测试路由
 * 用于验证CORS配置在各种场景下的表现
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 正常响应测试
Route::get("/test/normal", function () {
    return response()->json([
        "message" => "正常响应",
        "timestamp" => time()
    ]);
});

// 404错误测试
Route::get("/test/not-found", function () {
    return response()->json([
        "message" => "资源未找到"
    ], 404);
});

// 500错误测试
Route::get("/test/server-error", function () {
    throw new \Exception("服务器内部错误", 500);
});

// 401错误测试
Route::get("/test/unauthorized", function () {
    return response()->json([
        "message" => "未授权访问"
    ], 401);
});

// OPTIONS预检请求测试
Route::options("/test/{any}", function () {
    return response()->json([], 200);
})->where("any", ".*");