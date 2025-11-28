# TFSHOP 全局CORS配置文档

## 概述

本文档描述了TFSHOP项目中服务端API的全局跨域资源共享(CORS)配置实现。该配置确保前端应用能够跨域访问所有接口资源，并在各种错误场景下保持CORS头信息的一致性。

## 核心特性

### 1. 完整的CORS策略支持
- ✅ 支持所有HTTP方法（GET, POST, PUT, DELETE, OPTIONS, PATCH）
- ✅ 支持自定义请求头
- ✅ 支持凭据传输（Cookies、Authorization等）
- ✅ 支持预检请求缓存
- ✅ 支持多域名来源

### 2. 错误状态码CORS处理
- ✅ 404错误时保持CORS头信息
- ✅ 500错误时保持CORS头信息
- ✅ 401错误时保持CORS头信息
- ✅ 其他HTTP错误状态码下CORS头信息一致性

### 3. 预检请求处理
- ✅ OPTIONS请求自动响应
- ✅ 预检请求缓存优化（24小时）
- ✅ 请求方法和请求头验证

## 文件结构

```
api/
├── app/
│   ├── Http/
│   │   └── Middleware/
│   │       └── EnableCrossRequestMiddleware.php  # 增强的CORS中间件
│   ├── Exceptions/
│   │   └── Handler.php                          # 异常处理器CORS支持
│   └── Services/
│       └── CorsService.php                      # CORS服务类
├── config/
│   └── cors.php                                # CORS配置文件
└── routes/
    └── test_cors.php                             # CORS测试路由
```

## 配置详解

### 1. CORS配置文件 (config/cors.php)

```php
return [
    // 应用CORS的路径模式
    'paths' => ['api/*', 'admin/*', 'app/*'],
    
    // 允许的HTTP方法
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
    
    // 允许的来源域名
    'allowed_origins' => ['*'],
    
    // 允许的请求头
    'allowed_headers' => [
        'Origin', 'Content-Type', 'Cookie', 'X-CSRF-TOKEN', 'Accept',
        'Authorization', 'applyid', 'openid', 'apply-secret', 'versionid',
        'X-XSRF-TOKEN', 'Lang', 'X-Requested-With', 'X-HTTP-Method-Override'
    ],
    
    // 暴露的响应头
    'exposed_headers' => ['Authorization', 'authenticated', 'X-Total-Count'],
    
    // 预检请求缓存时间（秒）
    'max_age' => 86400, // 24小时
    
    // 是否支持凭据
    'supports_credentials' => true,
];
```

### 2. 增强的CORS中间件 (app/Http/Middleware/EnableCrossRequestMiddleware.php)

该中间件提供了以下增强功能：

- **异常处理**: 即使在发生异常的情况下也能确保CORS头被正确添加
- **预检请求处理**: 自动处理OPTIONS请求并返回适当的CORS头
- **来源验证**: 支持通配符和具体域名验证
- **配置灵活**: 可通过配置数组自定义CORS行为

### 3. 异常处理器CORS支持 (app/Exceptions/Handler.php)

在异常处理中添加了CORS头信息，确保错误响应也包含正确的CORS头：

- 404 Not Found
- 500 Internal Server Error  
- 401 Unauthorized
- 其他HTTP错误状态码

### 4. CORS服务类 (app/Services/CorsService.php)

提供可重用的CORS服务功能：

- `addCorsHeaders()`: 为响应添加CORS头
- `handlePreflightRequest()`: 处理预检请求
- `createErrorResponse()`: 创建带CORS头的错误响应
- `getConfig()/setConfig()`: 获取和设置CORS配置

## 响应头说明

当CORS配置生效时，所有响应将包含以下头信息：

```http
Access-Control-Allow-Origin: http://example.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH
Access-Control-Allow-Headers: Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, applyid, openid, apply-secret, versionid, X-XSRF-TOKEN, Lang, X-Requested-With
Access-Control-Expose-Headers: Authorization, authenticated, X-Total-Count
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400
```

## 测试验证

项目包含完整的CORS测试工具：

### 测试脚本 (test_cors_enhanced.php)

该脚本验证以下场景：

1. **预检请求测试**: 验证OPTIONS请求的正确处理
2. **正常请求测试**: 验证GET/POST等请求的CORS头
3. **错误状态码测试**: 验证404/500/401等错误下的CORS头一致性
4. **凭据支持测试**: 验证Cookie和Authorization头的支持

### 运行测试

```bash
php test_cors_enhanced.php
```

## 安全建议

### 生产环境配置

在生产环境中，建议将`allowed_origins`从通配符`*`改为具体的域名：

```php
'allowed_origins' => [
    'https://yourdomain.com',
    'https://app.yourdomain.com'
],
```

### 允许的请求头

只允许必要的请求头，避免使用通配符：

```php
'allowed_headers' => [
    'Origin',
    'Content-Type',
    'Accept',
    'Authorization',
    'X-Requested-With'
],
```

### 监控和日志

建议添加CORS相关的监控和日志记录，以便追踪跨域请求情况：

```php
// 在中间件中添加日志
\Log::info('CORS Request', [
    'origin' => $request->header('Origin'),
    'method' => $request->method(),
    'path' => $request->path()
]);
```

## 故障排查

### 常见问题

1. **CORS头缺失**: 检查中间件是否在Kernel中正确注册
2. **预检请求失败**: 验证OPTIONS请求是否返回200状态码
3. **凭据传输失败**: 确保`Access-Control-Allow-Credentials`设置为true
4. **特定来源被拒绝**: 检查`allowed_origins`配置

### 调试步骤

1. 使用浏览器开发者工具查看网络请求头
2. 检查服务器响应头是否包含CORS相关头信息
3. 运行测试脚本验证配置正确性
4. 查看Laravel日志获取详细错误信息

## 性能优化

### 预检请求缓存

通过设置`Access-Control-Max-Age`头，可以减少预检请求的频率：

```php
'max_age' => 86400 // 24小时缓存
```

### 中间件顺序

确保CORS中间件在全局中间件栈中的正确位置，通常放在较前的位置：

```php
protected $middleware = [
    \App\Http\Middleware\EnableCrossRequestMiddleware::class,
    // 其他中间件...
];
```

## 更新记录

- **2024-01**: 初始版本，实现基础CORS配置
- **2024-12**: 增强错误处理，添加CORS服务类
- **2025-01**: 完善测试工具，优化预检请求处理

## 相关资源

- [MDN CORS文档](https://developer.mozilla.org/zh-CN/docs/Web/HTTP/CORS)
- [Laravel CORS包](https://github.com/fruitcake/laravel-cors)
- [W3C CORS规范](https://www.w3.org/TR/cors/)