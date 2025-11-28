<?php
/**
 * CORS配置测试脚本
 * 用于验证全局CORS配置在各种错误场景下的表现
 */

// 设置测试配置
$testConfig = [
    'base_url' => 'http://localhost:8000',
    'test_origins' => [
        'http://localhost:3000',
        'http://localhost:8080', 
        'https://example.com',
        'http://test.com'
    ],
    'endpoints' => [
        'normal' => '/api/test/normal',
        'not_found' => '/api/test/not-found',
        'server_error' => '/api/test/server-error',
        'unauthorized' => '/api/test/unauthorized'
    ]
];

/**
 * 发送HTTP请求并返回响应信息
 */
function sendRequest($url, $method = 'GET', $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    return [
        'headers' => $header,
        'body' => $body,
        'http_code' => $httpCode
    ];
}

/**
 * 解析响应头
 */
function parseHeaders($headerText) {
    $headers = [];
    $lines = explode("\n", $headerText);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $headers[strtolower(trim($key))] = trim($value);
        }
    }
    
    return $headers;
}

/**
 * 测试CORS配置
 */
function testCorsConfiguration($config) {
    echo "=== CORS配置测试开始 ===\n\n";
    
    $totalTests = 0;
    $passedTests = 0;
    
    // 测试预检请求
    echo "1. 测试预检请求(OPTIONS):\n";
    foreach ($config['test_origins'] as $origin) {
        $totalTests++;
        echo "   测试来源: $origin\n";
        
        $headers = [
            'Origin: ' . $origin,
            'Access-Control-Request-Method: POST',
            'Access-Control-Request-Headers: Content-Type, Authorization'
        ];
        
        $result = sendRequest($config['base_url'] . $config['endpoints']['normal'], 'OPTIONS', $headers);
        $parsedHeaders = parseHeaders($result['headers']);
        
        echo "   HTTP状态码: " . $result['http_code'] . "\n";
        echo "   Access-Control-Allow-Origin: " . ($parsedHeaders['access-control-allow-origin'] ?? '未设置') . "\n";
        echo "   Access-Control-Allow-Methods: " . ($parsedHeaders['access-control-allow-methods'] ?? '未设置') . "\n";
        echo "   Access-Control-Allow-Headers: " . ($parsedHeaders['access-control-allow-headers'] ?? '未设置') . "\n";
        echo "   Access-Control-Max-Age: " . ($parsedHeaders['access-control-max-age'] ?? '未设置') . "\n";
        
        if ($result['http_code'] == 200 && 
            isset($parsedHeaders['access-control-allow-origin']) &&
            isset($parsedHeaders['access-control-allow-methods'])) {
            echo "   ✓ 预检请求测试通过\n";
            $passedTests++;
        } else {
            echo "   ✗ 预检请求测试失败\n";
        }
        echo "\n";
    }
    
    // 测试正常请求
    echo "2. 测试正常请求:\n";
    foreach ($config['test_origins'] as $origin) {
        $totalTests++;
        echo "   测试来源: $origin\n";
        
        $headers = [
            'Origin: ' . $origin,
            'Content-Type: application/json'
        ];
        
        $result = sendRequest($config['base_url'] . $config['endpoints']['normal'], 'GET', $headers);
        $parsedHeaders = parseHeaders($result['headers']);
        
        echo "   HTTP状态码: " . $result['http_code'] . "\n";
        echo "   Access-Control-Allow-Origin: " . ($parsedHeaders['access-control-allow-origin'] ?? '未设置') . "\n";
        echo "   Access-Control-Allow-Credentials: " . ($parsedHeaders['access-control-allow-credentials'] ?? '未设置') . "\n";
        
        if (isset($parsedHeaders['access-control-allow-origin'])) {
            echo "   ✓ 正常请求CORS头设置正确\n";
            $passedTests++;
        } else {
            echo "   ✗ 正常请求CORS头缺失\n";
        }
        echo "\n";
    }
    
    // 测试错误状态码下的CORS处理
    echo "3. 测试错误状态码下的CORS处理:\n";
    
    $errorTests = [
        '404错误' => $config['endpoints']['not_found'],
        '500错误' => $config['endpoints']['server_error'],
        '401错误' => $config['endpoints']['unauthorized']
    ];
    
    foreach ($errorTests as $errorType => $endpoint) {
        echo "   测试$errorType:\n";
        $totalTests++;
        
        $headers = [
            'Origin: ' . $config['test_origins'][0],
            'Content-Type: application/json'
        ];
        
        $result = sendRequest($config['base_url'] . $endpoint, 'GET', $headers);
        $parsedHeaders = parseHeaders($result['headers']);
        
        echo "   HTTP状态码: " . $result['http_code'] . "\n";
        echo "   Access-Control-Allow-Origin: " . ($parsedHeaders['access-control-allow-origin'] ?? '未设置') . "\n";
        echo "   Access-Control-Allow-Credentials: " . ($parsedHeaders['access-control-allow-credentials'] ?? '未设置') . "\n";
        
        if (isset($parsedHeaders['access-control-allow-origin'])) {
            echo "   ✓ 错误状态码下CORS头设置正确\n";
            $passedTests++;
        } else {
            echo "   ✗ 错误状态码下CORS头缺失\n";
        }
        echo "\n";
    }
    
    // 测试凭据支持
    echo "4. 测试凭据支持:\n";
    $totalTests++;
    
    $headers = [
        'Origin: ' . $config['test_origins'][0],
        'Content-Type: application/json'
    ];
    
    $result = sendRequest($config['base_url'] . $config['endpoints']['normal'], 'GET', $headers);
    $parsedHeaders = parseHeaders($result['headers']);
    
    echo "   Access-Control-Allow-Credentials: " . ($parsedHeaders['access-control-allow-credentials'] ?? '未设置') . "\n";
    
    if (isset($parsedHeaders['access-control-allow-credentials']) && 
        $parsedHeaders['access-control-allow-credentials'] === 'true') {
        echo "   ✓ 凭据支持已启用\n";
        $passedTests++;
    } else {
        echo "   ✗ 凭据支持未启用\n";
    }
    echo "\n";
    
    // 总结
    echo "=== 测试结果总结 ===\n";
    echo "总测试数: $totalTests\n";
    echo "通过测试: $passedTests\n";
    echo "失败测试: " . ($totalTests - $passedTests) . "\n";
    echo "通过率: " . round(($passedTests / $totalTests) * 100, 2) . "%\n";
    
    if ($passedTests === $totalTests) {
        echo "✓ 所有测试通过！CORS配置正确。\n";
    } else {
        echo "✗ 部分测试失败，请检查CORS配置。\n";
    }
}

/**
 * 创建测试用的API路由
 */
function createTestRoutes() {
    $routeContent = '<?php

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
';
    
    file_put_contents('routes/test_cors.php', $routeContent);
    echo "测试路由文件已创建: routes/test_cors.php\n";
}

// 执行测试
echo "CORS配置测试工具\n";
echo "==================\n\n";

if ($argc > 1 && $argv[1] === '--create-routes') {
    createTestRoutes();
} else {
    testCorsConfiguration($testConfig);
}