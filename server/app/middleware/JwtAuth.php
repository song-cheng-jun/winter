<?php
declare (strict_types = 1);

namespace app\middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\Request;
use think\Response;

/**
 * JWT认证中间件
 * 用于验证JWT Token的有效性
 *
 * 使用方法：
 * 1. 在路由或控制器中注册中间件
 * 2. 请求时需要在Header中携带 Authorization: Bearer {token}
 *
 * Token验证失败会返回以下错误：
 * - UNAUTHORIZED: 未携带Token
 * - TOKEN_EXPIRED: Token已过期
 * - TOKEN_INVALID: Token签名无效
 * - TOKEN_ERROR: Token验证失败（其他错误）
 */
class JwtAuth
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
        // 获取Token（从Header中获取）
        $token = $request->header('authorization');

        // 如果没有携带Token，返回401未授权
        if (empty($token)) {
            return json([
                'success' => false,
                'message' => '请先登录',
                'error' => 'UNAUTHORIZED',
                'error_code' => 401
            ], 401);
        }

        // 移除 "Bearer " 前缀（标准Token格式）
        $token = str_replace('Bearer ', '', $token);

        try {
            // 获取JWT配置
            $secret = config('jwt.secret');

            // 解析JWT Token
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $data = (array)$decoded;

            // 将用户信息存储到请求对象中，方便后续使用
            $request->userId = $data['user_id'] ?? null;
            $request->userInfo = $data['user_info'] ?? [];

            // 继续执行请求
            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            // Token过期
            return json([
                'success' => false,
                'message' => '登录已过期，请重新登录',
                'error' => 'TOKEN_EXPIRED',
                'error_code' => 401
            ], 401);

        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            // Token签名无效
            return json([
                'success' => false,
                'message' => 'Token签名无效',
                'error' => 'TOKEN_INVALID',
                'error_code' => 401
            ], 401);

        } catch (\Exception $e) {
            // 其他错误
            return json([
                'success' => false,
                'message' => 'Token验证失败',
                'error' => $e->getMessage(),
                'error_code' => 401
            ], 401);
        }
    }
}
