<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\Menu;
use Firebase\JWT\JWT;
use think\facade\Request;

/**
 * 认证控制器
 * 负责用户登录、登出、获取用户信息等认证相关功能
 *
 * 接口列表：
 * - POST /api/auth/login - 用户登录
 * - GET /api/auth/userinfo - 获取用户信息（需要Token）
 * - POST /api/auth/logout - 用户登出（需要Token）
 */
class AuthController extends BaseController
{
    /**
     * 用户登录
     *
     * @return \think\response\Json
     *
     * 请求方式: POST
     * 请求路径: /api/auth/login
     * 请求参数:
     *   - username: string (必填) 用户名
     *   - password: string (必填) 密码
     *
     * 返回数据:
     *   success: true/false 是否成功
     *   message: string 提示信息
     *   data: object
     *     - token: string JWT Token
     *     - userInfo: object 用户信息
     *
     * 示例:
     * POST /api/auth/login
     * {
     *   "username": "admin",
     *   "password": "password"
     * }
     */
    public function login()
    {
        try {
            // 获取请求参数
            $username = input('username', '');
            $password = input('password', '');

            // 参数验证
            if (empty($username)) {
                return json([
                    'success' => false,
                    'message' => '用户名不能为空',
                    'error' => 'USERNAME_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($password)) {
                return json([
                    'success' => false,
                    'message' => '密码不能为空',
                    'error' => 'PASSWORD_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            // 查找用户
            $user = User::findByUsername($username);

            // 用户不存在
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户名或密码错误',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 401
                ], 401);
            }

            // 检查用户状态
            if ($user->status != 1) {
                return json([
                    'success' => false,
                    'message' => '该账号已被禁用',
                    'error' => 'USER_DISABLED',
                    'error_code' => 403
                ], 403);
            }

            // 验证密码
            if (!User::verifyPassword($password, $user->password)) {
                return json([
                    'success' => false,
                    'message' => '用户名或密码错误',
                    'error' => 'PASSWORD_INVALID',
                    'error_code' => 401
                ], 401);
            }

            // 获取客户端IP
            $clientIp = Request::ip();

            // 更新最后登录信息
            User::updateLastLogin($user->id, $clientIp);

            // 生成JWT Token
            $token = $this->generateToken($user->id, $user->toArray());

            // 获取用户信息（不包含敏感字段）
            $userInfo = User::getUserInfo($user->id);

            // 返回成功响应
            return json([
                'success' => true,
                'message' => '登录成功',
                'data' => [
                    'token' => $token,
                    'userInfo' => $userInfo
                ]
            ]);

        } catch (\Exception $e) {
            // 捕获异常，返回错误信息
            return json([
                'success' => false,
                'message' => '登录失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取当前登录用户信息
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/auth/userinfo
     * 请求头:
     *   - Authorization: Bearer {token}
     *
     * 返回数据:
     *   success: true/false 是否成功
     *   message: string 提示信息
     *   data: object 用户信息
     *
     * 示例:
     * GET /api/auth/userinfo
     * Headers: Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
     */
    public function getUserInfo()
    {
        try {
            // 从中间件中获取用户ID（中间件已验证Token）
            $userId = Request::instance()->userId ?? null;

            if (!$userId) {
                return json([
                    'success' => false,
                    'message' => '用户未登录',
                    'error' => 'USER_NOT_LOGGED_IN',
                    'error_code' => 401
                ], 401);
            }

            // 获取用户信息
            $userInfo = User::getUserInfo($userId);

            if (!$userInfo) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 返回用户信息
            return json([
                'success' => true,
                'message' => '获取用户信息成功',
                'data' => $userInfo
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户信息失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 用户登出
     *
     * @return \think\response\Json
     *
     * 请求方式: POST
     * 请求路径: /api/auth/logout
     * 请求头:
     *   - Authorization: Bearer {token}
     *
     * 返回数据:
     *   success: true/false 是否成功
     *   message: string 提示信息
     *
     * 注意：
     * JWT是无状态的，服务端无法主动销毁Token
     * 实际登出操作在前端，删除本地存储的Token即可
     * 这里主要用于记录登出日志或执行其他清理操作
     */
    public function logout()
    {
        try {
            // 获取用户ID
            $userId = Request::instance()->userId ?? null;

            // 这里可以记录登出日志
            // 例如：记录用户登出时间、IP等信息

            return json([
                'success' => true,
                'message' => '登出成功'
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '登出失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 生成JWT Token
     *
     * @param int $userId 用户ID
     * @param array $userInfo 用户信息
     * @return string JWT Token
     */
    private function generateToken(int $userId, array $userInfo): string
    {
        // 获取JWT配置
        $config = config('jwt');
        $secret = $config['secret'];
        $expire = $config['expire'];
        $iss = $config['iss'];
        $aud = $config['aud'];

        // 计算过期时间
        $expireTime = time() + $expire;

        // 构建Token载荷
        $payload = [
            'iss' => $iss,                    // 签发者
            'aud' => $aud,                    // 受众
            'iat' => time(),                  // 签发时间
            'exp' => $expireTime,             // 过期时间
            'user_id' => $userId,             // 用户ID
            'user_info' => [                  // 用户基本信息
                'username' => $userInfo['username'] ?? '',
                'nickname' => $userInfo['nickname'] ?? '',
                'role' => $userInfo['role'] ?? 'user',
            ]
        ];

        // 生成Token
        return JWT::encode($payload, $secret, 'HS256');
    }

    /**
     * 获取当前用户的菜单树
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/auth/menus
     * 请求头:
     *   - Authorization: Bearer {token}
     *
     * 返回数据:
     *   success: true/false
     *   message: string
     *   data: array 菜单树形结构
     */
    public function getUserMenus()
    {
        try {
            $userId = Request::instance()->userId ?? null;

            if (!$userId) {
                return json([
                    'success' => false,
                    'message' => '用户未登录',
                    'error' => 'USER_NOT_LOGGED_IN',
                    'error_code' => 401
                ], 401);
            }

            // 获取用户的菜单ID列表
            $menuIds = User::getMenuIds($userId);

            if (empty($menuIds)) {
                return json([
                    'success' => true,
                    'message' => '获取用户菜单成功',
                    'data' => []
                ]);
            }

            // 获取菜单树
            $menus = Menu::getTree(0, $menuIds);

            return json([
                'success' => true,
                'message' => '获取用户菜单成功',
                'data' => $menus
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户菜单失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取当前用户的权限列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/auth/permissions
     * 请求头:
     *   - Authorization: Bearer {token}
     *
     * 返回数据:
     *   success: true/false
     *   message: string
     *   data: array 权限代码数组
     */
    public function getUserPermissions()
    {
        try {
            $userId = Request::instance()->userId ?? null;

            if (!$userId) {
                return json([
                    'success' => false,
                    'message' => '用户未登录',
                    'error' => 'USER_NOT_LOGGED_IN',
                    'error_code' => 401
                ], 401);
            }

            // 获取用户的权限代码列表
            $permissions = User::getPermissionCodes($userId);

            return json([
                'success' => true,
                'message' => '获取用户权限成功',
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户权限失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取当前用户完整信息（包含角色和权限）
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/auth/info
     * 请求头:
     *   - Authorization: Bearer {token}
     *
     * 返回数据:
     *   success: true/false
     *   message: string
     *   data: object 用户信息（包含角色、菜单、权限）
     */
    public function getUserCompleteInfo()
    {
        try {
            $userId = Request::instance()->userId ?? null;

            if (!$userId) {
                return json([
                    'success' => false,
                    'message' => '用户未登录',
                    'error' => 'USER_NOT_LOGGED_IN',
                    'error_code' => 401
                ], 401);
            }

            // 获取用户信息（包含角色）
            $userInfo = User::getUserInfo($userId, true);

            if (!$userInfo) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 获取用户的菜单
            $menuIds = User::getMenuIds($userId);
            $menus = !empty($menuIds) ? Menu::getTree(0, $menuIds) : [];

            // 获取用户的权限代码
            $permissions = User::getPermissionCodes($userId);

            // 组装完整信息
            $data = [
                'user' => $userInfo,
                'roles' => $userInfo['roles'] ?? [],
                'menus' => $menus,
                'permissions' => $permissions
            ];

            // 移除 user 中的 roles 字段（因为已经在顶层返回了）
            unset($data['user']['roles']);

            return json([
                'success' => true,
                'message' => '获取用户信息成功',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户信息失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }
}
