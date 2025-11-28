<?php

/**
 * CORS测试脚本 - 验证认证失败时的CORS处理
 */

// 测试配置
$testUrl = 'http://localhost:8000/api/admin/dashboard';  // 使用现有的管理员认证端点
$testOrigin = 'https://example.com';

echo "=== CORS认证失败测试 ===\n\n";

// 测试1: 未认证的GET请求
echo "测试1: 未认证的GET请求\n";
echo "URL: $testUrl\n";
echo "Origin: $testOrigin\n\n";

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: ' . $testOrigin,
    'Accept: application/json',
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应头:\n";
echo $headers;
echo "\n响应体:\n";
echo $body;
echo "\n" . str_repeat("=", 50) . "\n\n";

// 测试2: 带有无效Token的请求
echo "测试2: 带有无效Token的请求\n";
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: ' . $testOrigin,
    'Accept: application/json',
    'Content-Type: application/json',
    'Authorization: Bearer invalid_token_12345'
]);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应头:\n";
echo $headers;
echo "\n响应体:\n";
echo $body;
echo "\n" . str_repeat("=", 50) . "\n\n";

// 测试3: 预检请求
echo "测试3: 预检请求\n";
$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: ' . $testOrigin,
    'Access-Control-Request-Method: POST',
    'Access-Control-Request-Headers: Content-Type, Authorization'
]);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);


curl_close($ch);

echo "HTTP状态码: $httpCode\n";
echo "响应头:\n";
echo $headers;
echo "\n" . str_repeat("=", 50) . "\n\n";

// 测试结果分析
echo "=== 测试结果分析 ===\n";
if ($httpCode == 401) {
    echo "✓ 认证失败正确处理\n";
} else {
    echo "✗ 认证失败处理异常\n";
}

if (strpos($headers, 'Access-Control-Allow-Origin') !== false) {
    echo "✓ CORS头正确设置\n";
} else {
    echo "✗ CORS头缺失\n";
}

echo "\n测试完成！\n";