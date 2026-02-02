<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Request;
use think\Response;

/**
 * CORS跨域中间件
 * 用于处理跨域请求
 *
 * 功能说明：
 * 1. 允许所有来源的跨域请求
 * 2. 支持所有常用的HTTP方法
 * 3. 允许携带自定义请求头（如Authorization）
 * 4. 正确处理OPTIONS预检请求
 */
class Cors
{
    /**
     * 处理请求
     *
     * @param Request $request 请求对象
     * @param \Closure $next 下一个中间件
     * @return Response
     */
    public function handle(Request $request, \Closure $next)
    {
        // 获取请求来源
        $origin = $request->header('origin') ?? '*';

        // 处理OPTIONS预检请求（在处理请求之前直接返回）
        if ($request->method() == 'OPTIONS') {
            return Response::create('', 'html', 204)->header([
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, Accept, Origin',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        // 获取响应对象
        $response = $next($request);

        // 设置CORS响应头
        $response->header([
            // 允许的来源（开发环境使用*，生产环境建议指定具体域名）
            'Access-Control-Allow-Origin' => $origin,

            // 允许的HTTP方法
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',

            // 允许的请求头
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, X-Requested-With, Accept, Origin',

            // 允许发送凭证（如cookies）
            'Access-Control-Allow-Credentials' => 'true',

            // 预检请求缓存时间（秒）
            'Access-Control-Max-Age' => '86400',
        ]);

        return $response;
    }
}
