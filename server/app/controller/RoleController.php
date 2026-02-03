<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Role;
use app\model\RolePermission;
use app\model\RoleMenu;
use app\model\UserRole;
use app\model\Permission;
use app\model\Menu;
use think\facade\Request;

/**
 * 角色管理控制器
 * 负责角色的增删改查、权限分配、菜单分配等功能
 */
class RoleController extends BaseController
{
    /**
     * 获取角色列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/roles
     * 请求参数:
     *   - page: int (可选) 页码，默认1
     *   - limit: int (可选) 每页数量，默认20
     *   - keyword: string (可选) 搜索关键词
     *   - status: int (可选) 状态筛选
     */
    public function index()
    {
        try {
            $page = input('page', 1, 'int');
            $limit = input('limit', 20, 'int');
            $keyword = input('keyword', '', 'trim');
            $status = input('status', '', 'int');

            $query = Role::field('id,name,code,description,sort,status,created_at,updated_at');

            if (!empty($keyword)) {
                $query->whereLike('name|code|description', '%' . $keyword . '%');
            }

            if ($status !== '') {
                $query->where('status', $status);
            }

            $total = $query->count();
            $list = $query->order('sort', 'asc')
                ->order('id', 'desc')
                ->page($page, $limit)
                ->select()
                ->toArray();

            return json([
                'success' => true,
                'message' => '获取角色列表成功',
                'data' => [
                    'list' => $list,
                    'total' => $total,
                    'page' => $page,
                    'limit' => $limit
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取角色列表失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取角色详情
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/roles/:id
     */
    public function read()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            $role = Role::find($id);

            if (!$role) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            return json([
                'success' => true,
                'message' => '获取角色详情成功',
                'data' => $role->toArray()
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取角色详情失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 创建角色
     *
     * @return \think\response\Json
     *
     * 请求方式: POST
     * 请求路径: /api/roles
     * 请求参数:
     *   - name: string (必填) 角色名称
     *   - code: string (必填) 角色代码
     *   - description: string (可选) 角色描述
     *   - sort: int (可选) 排序
     */
    public function save()
    {
        try {
            $data = Request::only(['name', 'code', 'description', 'sort'], 'post');

            if (empty($data['name'])) {
                return json([
                    'success' => false,
                    'message' => '角色名称不能为空',
                    'error' => 'NAME_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($data['code'])) {
                return json([
                    'success' => false,
                    'message' => '角色代码不能为空',
                    'error' => 'CODE_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            // 检查角色代码是否已存在
            if (Role::where('code', $data['code'])->find()) {
                return json([
                    'success' => false,
                    'message' => '角色代码已存在',
                    'error' => 'CODE_EXISTS',
                    'error_code' => 400
                ], 400);
            }

            $role = Role::create($data);

            return json([
                'success' => true,
                'message' => '创建角色成功',
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '创建角色失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 更新角色信息
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/roles/:id
     */
    public function update()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            $data = Request::only(['name', 'description', 'sort', 'status'], 'put');

            $role = Role::find($id);
            if (!$role) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $role->save($data);

            return json([
                'success' => true,
                'message' => '更新角色成功',
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '更新角色失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 删除角色
     *
     * @return \think\response\Json
     *
     * 请求方式: DELETE
     * 请求路径: /api/roles/:id
     */
    public function delete()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            $role = Role::find($id);
            if (!$role) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $role->delete();

            return json([
                'success' => true,
                'message' => '删除角色成功'
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '删除角色失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取角色的权限列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/roles/:id/permissions
     */
    public function getPermissions()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            if (!Role::find($id)) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $permissionIds = RolePermission::getRolePermissionIds($id);

            if (empty($permissionIds)) {
                return json([
                    'success' => true,
                    'message' => '获取角色权限成功',
                    'data' => []
                ]);
            }

            $permissions = Permission::whereIn('id', $permissionIds)
                ->order('sort', 'asc')
                ->select()
                ->toArray();

            return json([
                'success' => true,
                'message' => '获取角色权限成功',
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取角色权限失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 为角色分配权限
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/roles/:id/permissions
     * 请求参数:
     *   - permission_ids: array 权限ID数组
     */
    public function assignPermissions()
    {
        try {
            $id = input('id', 0, 'int');
            $permissionIds = input('permission_ids', [], 'array');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            if (!Role::find($id)) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 验证权限ID是否存在
            if (!empty($permissionIds)) {
                $validIds = Permission::whereIn('id', $permissionIds)->column('id');
                if (count($validIds) !== count($permissionIds)) {
                    return json([
                        'success' => false,
                        'message' => '存在无效的权限ID',
                        'error' => 'INVALID_PERMISSION_IDS',
                        'error_code' => 400
                    ], 400);
                }
            }

            RolePermission::assignPermissions($id, $permissionIds);

            return json([
                'success' => true,
                'message' => '分配权限成功',
                'data' => [
                    'role_id' => $id,
                    'permission_ids' => $permissionIds
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '分配权限失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取角色的菜单列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/roles/:id/menus
     */
    public function getMenus()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            if (!Role::find($id)) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $menuIds = RoleMenu::getRoleMenuIds($id);

            if (empty($menuIds)) {
                return json([
                    'success' => true,
                    'message' => '获取角色菜单成功',
                    'data' => []
                ]);
            }

            $menus = Menu::whereIn('id', $menuIds)
                ->order('sort', 'asc')
                ->select()
                ->toArray();

            return json([
                'success' => true,
                'message' => '获取角色菜单成功',
                'data' => $menus
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取角色菜单失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 为角色分配菜单
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/roles/:id/menus
     * 请求参数:
     *   - menu_ids: array 菜单ID数组
     */
    public function assignMenus()
    {
        try {
            $id = input('id', 0, 'int');
            $menuIds = input('menu_ids', [], 'array');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            if (!Role::find($id)) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 验证菜单ID是否存在
            if (!empty($menuIds)) {
                $validIds = Menu::whereIn('id', $menuIds)->column('id');
                if (count($validIds) !== count($menuIds)) {
                    return json([
                        'success' => false,
                        'message' => '存在无效的菜单ID',
                        'error' => 'INVALID_MENU_IDS',
                        'error_code' => 400
                    ], 400);
                }
            }

            RoleMenu::assignMenus($id, $menuIds);

            return json([
                'success' => true,
                'message' => '分配菜单成功',
                'data' => [
                    'role_id' => $id,
                    'menu_ids' => $menuIds
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '分配菜单失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取拥有该角色的用户列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/roles/:id/users
     */
    public function getUsers()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '角色ID无效',
                    'error' => 'INVALID_ROLE_ID',
                    'error_code' => 400
                ], 400);
            }

            if (!Role::find($id)) {
                return json([
                    'success' => false,
                    'message' => '角色不存在',
                    'error' => 'ROLE_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $userIds = UserRole::where('role_id', $id)->column('user_id');

            if (empty($userIds)) {
                return json([
                    'success' => true,
                    'message' => '获取角色用户成功',
                    'data' => []
                ]);
            }

            $users = \app\model\User::whereIn('id', $userIds)
                ->field('id,username,nickname,avatar,email,phone,status')
                ->select()
                ->toArray();

            return json([
                'success' => true,
                'message' => '获取角色用户成功',
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取角色用户失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }
}
