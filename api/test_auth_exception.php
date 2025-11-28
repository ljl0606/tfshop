<?php

/**
 * 直接测试认证异常处理
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 创建一个模拟的未认证请求
$request = Illuminate\Http\Request::create('/api/v1/admin/userInfo', 'GET');
$request->headers->set('Origin', 'https://example.com');
$request->headers->set('Accept', 'application/json');

// 处理请求
$response = $kernel->handle($request);

echo "=== 认证异常处理测试 ===\n\n";
echo "请求URL: /api/v1/admin/userInfo\n";
echo "请求Origin: https://example.com\n\n";

echo "响应状态码: " . $response->getStatusCode() . "\n";
echo "响应头:\n";

foreach ($response->headers->all() as $name => $values) {
    foreach ($values as $value) {
        echo "  $name: $value\n";
    }
}

echo "\n响应体:\n";
echo $response->getContent() . "\n";

echo "\n=== 结果分析 ===\n";
if ($response->getStatusCode() === 401) {
    echo "✓ 认证失败正确处理\n";
} else {
    echo "✗ 认证失败处理异常\n";
}

if ($response->headers->has('Access-Control-Allow-Origin')) {
    echo "✓ CORS头正确设置\n";
} else {
    echo "✗ CORS头缺失\n";
}

echo "\n测试完成！\n";