<?php
namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    public function handle($request, Closure $next)
    {
        // 允许的来源（生产环境改为具体域名）
        $allowedOrigins = ['*'];
        $origin = $request->header('Origin');
        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }

        // 允许的请求方法
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

        // 允许的请求头
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

        // 允许携带 Cookie
        header('Access-Control-Allow-Credentials: true');

        // 处理 OPTIONS 预检请求（直接返回 200）
        if ($request->method() === 'OPTIONS') {
            return response()->json([], 200);
        }

        return $next($request);
    }
}
