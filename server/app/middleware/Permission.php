<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Request;
use think\Response;

/**
 * 权限验证中间件
 * 用于验证用户是否有权限访问当前接口
 */
class Permission
{
    /**
     * 权限代码与路由的映射表
     * 格式：'路由路径' => ['请求方法' => '权限代码']
     */
    protected $permissionMap = [
        // 用户管理
        'api/users' => ['get' => 'user:list', 'post' => 'user:create'],
        'api/users/:id' => ['get' => 'user:detail', 'put' => 'user:update', 'delete' => 'user:delete'],
        'api/users/:id/roles' => ['get' => 'user:list-roles', 'put' => 'user:assign-roles'],
        'api/users/:id/status' => ['put' => 'user:update-status'],
        'api/users/:id/password' => ['put' => 'user:reset-password'],

        // 角色管理
        'api/roles' => ['get' => 'role:list', 'post' => 'role:create'],
        'api/roles/:id' => ['get' => 'role:detail', 'put' => 'role:update', 'delete' => 'role:delete'],
        'api/roles/:id/permissions' => ['get' => 'role:list-permissions', 'put' => 'role:assign-permissions'],
        'api/roles/:id/menus' => ['get' => 'role:list-menus', 'put' => 'role:assign-menus'],
        'api/roles/:id/users' => ['get' => 'role:list-users'],

        // 菜单管理
        'api/menus' => ['get' => 'menu:list', 'post' => 'menu:create'],
        'api/menus/tree' => ['get' => 'menu:tree'],
        'api/menus/:id' => ['get' => 'menu:detail', 'put' => 'menu:update', 'delete' => 'menu:delete'],

        // 权限管理
        'api/permissions' => ['get' => 'permission:list', 'post' => 'permission:create'],
        'api/permissions/group' => ['get' => 'permission:group'],
        'api/permissions/:id' => ['get' => 'permission:detail', 'put' => 'permission:update', 'delete' => 'permission:delete'],
    ];

    /**
     * 超级管理员标识
     * 超级管理员拥有所有权限，无需检查
     */
    const SUPER_ADMIN_CODE = 'super_admin';

    /**
     * 处理请求
     *
     * @param Request $request 请求对象
     * @param \Closure $next 下一个中间件
     * @return Response
     */
    public function handle(Request $request, \Closure $next)
    {
        // 从 JwtAuth 中间件中获取用户ID
        $userId = $request->userId ?? null;

        if (!$userId) {
            return json([
                'success' => false,
                'message' => '用户未登录',
                'error' => 'UNAUTHORIZED',
                'error_code' => 401
            ], 401);
        }

        // 获取当前路由和方法
        $route = $request->pathinfo();
        $method = strtolower($request->method());

        // 检查是否需要权限验证
        $requiredPermission = $this->getRequiredPermission($route, $method);

        // 如果该路由不需要权限验证，直接放行
        if ($requiredPermission === null) {
            return $next($request);
        }

        // 检查用户是否为超级管理员
        if ($this->isSuperAdmin($userId)) {
            return $next($request);
        }

        // 检查用户是否有该权限
        if ($this->hasPermission($userId, $requiredPermission)) {
            return $next($request);
        }

        // 无权限访问
        return json([
            'success' => false,
            'message' => '无权限访问',
            'error' => 'PERMISSION_DENIED',
            'error_code' => 403
        ], 403);
    }

    /**
     * 获取路由所需的权限代码
     *
     * @param string $route 路由路径
     * @param string $method 请求方法
     * @return string|null 权限代码，null表示不需要权限验证
     */
    protected function getRequiredPermission(string $route, string $method): ?string
    {
        // 精确匹配
        if (isset($this->permissionMap[$route][$method])) {
            return $this->permissionMap[$route][$method];
        }

        // 模糊匹配（处理带参数的路由）
        foreach ($this->permissionMap as $pattern => $methods) {
            if (isset($methods[$method]) && $this->matchRoute($route, $pattern)) {
                return $methods[$method];
            }
        }

        return null;
    }

    /**
     * 路由匹配
     *
     * @param string $route 实际路由
     * @param string $pattern 路由模式
     * @return bool
     */
    protected function matchRoute(string $route, string $pattern): bool
    {
        // 将路由模式转换为正则表达式
        $regex = str_replace([':id', ':name'], ['\d+', '[^/]+'], $pattern);
        $regex = '#^' . $regex . '$#';

        return preg_match($regex, $route) === 1;
    }

    /**
     * 检查用户是否为超级管理员
     *
     * @param int $userId 用户ID
     * @return bool
     */
    protected function isSuperAdmin(int $userId): bool
    {
        try {
            $userRoles = \app\model\UserRole::where('user_id', $userId)
                ->with(['role' => function($query) {
                    $query->where('code', self::SUPER_ADMIN_CODE);
                }])
                ->select();

            foreach ($userRoles as $userRole) {
                if ($userRole->role && $userRole->role->code === self::SUPER_ADMIN_CODE) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 检查用户是否有指定权限
     *
     * @param int $userId 用户ID
     * @param string $permission 权限代码
     * @return bool
     */
    protected function hasPermission(int $userId, string $permission): bool
    {
        try {
            // 通过用户 -> 角色 -> 权限 查询
            $count = \app\model\UserRole::alias('ur')
                ->join('role_permissions rp', 'ur.role_id = rp.role_id')
                ->join('permissions p', 'rp.permission_id = p.id')
                ->where('ur.user_id', $userId)
                ->where('p.code', $permission)
                ->where('p.status', 1)
                ->count();

            return $count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
