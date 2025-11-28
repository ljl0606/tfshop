# CORS配置文档

## 概述
本文档描述了API目录下服务端接口的完整CORS（跨域资源共享）策略配置，确保前端应用能够跨域访问所有接口资源，并在各种错误场景下保持CORS头信息的一致性。

## 配置组件

### 1. 主要CORS中间件
**文件**: `app/Http/Middleware/CorsMiddleware.php`
- 处理所有跨域请求的CORS头设置
- 支持动态Origin验证
- 处理预检请求(OPTIONS)
- 在错误响应中保持CORS头一致性

### 2. 备用CORS中间件
**文件**: `app/Http/Middleware/EnableCrossRequestMiddleware.php`
- 提供额外的CORS支持
- 处理特殊场景下的跨域请求

### 3. 异常处理CORS支持
**文件**: `app/Exceptions/Handler.php`
- 确保500、404等错误状态码响应包含正确的CORS头
- 在异常情况下保持跨域请求的正常处理

### 4. CORS配置文件
**文件**: `config/cors.php`
- 定义全局CORS策略参数
- 配置允许的路径、方法、请求头
- 设置预检请求缓存时间和凭证支持

### 5. 中间件注册
**文件**: `app/Http/Kernel.php`
- 确保CORS中间件在请求处理链中优先执行
- 配置多个CORS中间件的执行顺序

## CORS头配置详情

### Access-Control-Allow-Origin
- **值**: 动态匹配请求中的Origin头
- **功能**: 允许指定的源访问资源
- **特殊处理**: 支持通配符(*)和具体域名列表

### Access-Control-Allow-Methods
- **值**: `GET, POST, PUT, DELETE, OPTIONS`
- **功能**: 指定允许的HTTP请求方法
- **应用场景**: 支持RESTful API的所有标准操作

### Access-Control-Allow-Headers
- **值**: `Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-TOKEN`
- **功能**: 指定允许的自定义请求头
- **扩展性**: 可根据需要添加额外的请求头

### Access-Control-Expose-Headers
- **值**: `Authorization, X-Custom-Header`
- **功能**: 指定浏览器可以访问的响应头
- **用途**: 允许前端获取特定的响应头信息

### Access-Control-Allow-Credentials
- **值**: `true`
- **功能**: 允许携带认证信息（如cookies）
- **安全**: 配合具体的Origin值使用，不支持通配符

### Access-Control-Max-Age
- **值**: `86400` (24小时)
- **功能**: 预检请求结果的缓存时间
- **优化**: 减少重复的预检请求，提高性能

## 错误处理机制

### 404错误处理
- 在资源不存在时返回404状态码
- 保持所有CORS头信息的完整性
- 确保后续跨域请求仍能正常进行

### 500错误处理
- 在服务器内部错误时返回500状态码
- 通过异常处理器添加CORS头
- 防止错误响应导致后续请求跨域失败

### 其他错误码
- 支持400、401、403等常见HTTP错误码
- 所有错误响应都包含完整的CORS头信息
- 保证错误响应的跨域一致性

## 测试端点

### CORS测试路由
基础路径: `/cors-test/`

#### 正常响应测试
- `GET /cors-test/normal` - 测试正常GET请求
- `POST /cors-test/post` - 测试POST请求
- `PUT /cors-test/put/{id}` - 测试PUT请求
- `DELETE /cors-test/delete/{id}` - 测试DELETE请求

#### 错误响应测试
- `GET /cors-test/not-found` - 测试404错误响应
- `GET /cors-test/server-error` - 测试500错误响应

#### 特殊测试
- `GET /cors-test/custom-headers` - 测试自定义响应头
- `GET /cors-test/authenticated` - 测试需要认证的请求
- `OPTIONS /cors-test/preflight` - 测试预检请求处理
- `GET /cors-test/config` - 获取当前CORS配置信息

## 使用示例

### 前端JavaScript调用示例
```javascript
// 正常的GET请求
fetch('http://your-api-domain/cors-test/normal', {
    method: 'GET',
    headers: {
        'Origin': 'http://your-frontend-domain'
    }
})
.then(response => response.json())
.then(data => console.log(data));

// 带认证的POST请求
fetch('http://your-api-domain/cors-test/post', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer your-token',
        'Origin': 'http://your-frontend-domain'
    },
    body: JSON.stringify({ key: 'value' }),
    credentials: 'include'
})
.then(response => response.json())
.then(data => console.log(data));

// 预检请求测试（浏览器会自动处理）
fetch('http://your-api-domain/cors-test/custom-headers', {
    method: 'GET',
    headers: {
        'X-Custom-Header': 'custom-value',
        'Origin': 'http://your-frontend-domain'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

### cURL测试命令
```bash
# 测试正常GET请求
 curl -H "Origin: http://localhost:3000" \
      -H "Content-Type: application/json" \
      -X GET http://your-api-domain/cors-test/normal

# 测试预检请求
 curl -H "Origin: http://localhost:3000" \
      -H "Access-Control-Request-Method: POST" \
      -H "Access-Control-Request-Headers: Content-Type, Authorization" \
      -X OPTIONS http://your-api-domain/cors-test/post

# 测试错误响应
 curl -H "Origin: http://localhost:3000" \
      -X GET http://your-api-domain/cors-test/not-found

# 测试500错误
 curl -H "Origin: http://localhost:3000" \
      -X GET http://your-api-domain/cors-test/server-error
```

## 安全配置建议

### 生产环境配置
1. **限制允许的源**: 将`allowedOrigins`从通配符(*)改为具体的域名列表
2. **验证请求头**: 严格验证`Access-Control-Request-Headers`中的自定义头
3. **监控和日志**: 记录异常的跨域请求尝试
4. **HTTPS强制**: 在生产环境中强制使用HTTPS协议

### 开发环境配置
1. **宽松的CORS策略**: 允许本地开发服务器的各种端口
2. **详细的错误信息**: 在开发模式下提供更详细的CORS错误信息
3. **测试覆盖**: 使用提供的测试端点验证各种场景

## 故障排除

### 常见问题和解决方案

#### 1. 预检请求失败
**症状**: OPTIONS请求返回403或没有CORS头
**解决**: 检查中间件注册顺序，确保CORS中间件优先执行

#### 2. 错误响应没有CORS头
**症状**: 404/500等错误导致后续请求跨域失败
**解决**: 检查异常处理器中的CORS头添加逻辑

#### 3. 认证信息无法传递
**症状**: Cookies或Authorization头无法发送到服务器
**解决**: 确认`Access-Control-Allow-Credentials`为`true`，且`Access-Control-Allow-Origin`不是通配符

#### 4. 自定义响应头不可见
**症状**: 前端无法获取特定的响应头
**解决**: 检查`Access-Control-Expose-Headers`配置是否包含需要的头

### 调试工具
1. **浏览器开发者工具**: Network面板查看请求和响应头
2. **Laravel日志**: 检查`storage/logs/laravel.log`中的错误信息
3. **CORS测试脚本**: 使用提供的测试端点进行验证

## 性能优化

### 预检请求缓存
- 设置合理的`Access-Control-Max-Age`值（24小时）
- 减少重复的OPTIONS请求
- 提高API响应速度

### 中间件优化
- 只对需要的路由应用CORS中间件
- 避免不必要的CORS头计算
- 使用缓存机制存储CORS配置

## 更新和维护

### 配置更新
- 定期检查CORS配置的安全性
- 根据业务需求调整允许的源和方法
- 更新文档以反映配置变更

### 安全审计
- 定期审查CORS策略的安全性
- 检查是否有未授权的源访问
- 监控异常的跨域请求模式

## 联系和支持

如有CORS配置相关问题，请联系开发团队或参考Laravel官方文档中的CORS配置指南。