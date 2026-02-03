<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Permission;
use app\model\Menu;
use think\facade\Request;

/**
 * 权限管理控制器
 * 负责权限的增删改查、分组查询等功能
 */
class PermissionController extends BaseController
{
    /**
     * 获取权限列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/permissions
     * 请求参数:
     *   - page: int (可选) 页码，默认1
     *   - limit: int (可选) 每页数量，默认20
     *   - keyword: string (可选) 搜索关键词
     *   - type: string (可选) 类型筛选（api/menu/button）
     *   - menu_id: int (可选) 菜单ID筛选
     */
    public function index()
    {
        try {
            $page = input('page', 1, 'int');
            $limit = input('limit', 20, 'int');
            $keyword = input('keyword', '', 'trim');
            $type = input('type', '', 'trim');
            $menuId = input('menu_id', '', 'int');

            $query = Permission::field('id,menu_id,name,code,type,description,sort,status,created_at,updated_at');

            if (!empty($keyword)) {
                $query->whereLike('name|code|description', '%' . $keyword . '%');
            }

            if (!empty($type)) {
                $query->where('type', $type);
            }

            if (!empty($menuId)) {
                $query->where('menu_id', $menuId);
            }

            $total = $query->count();
            $list = $query->order('sort', 'asc')
                ->order('id', 'desc')
                ->page($page, $limit)
                ->select()
                ->toArray();

            return json([
                'success' => true,
                'message' => '获取权限列表成功',
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
                'message' => '获取权限列表失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限分组列表（按菜单分组）
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/permissions/group
     */
    public function group()
    {
        try {
            $grouped = Permission::getGroupedList();

            return json([
                'success' => true,
                'message' => '获取权限分组成功',
                'data' => $grouped
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取权限分组失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取权限详情
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/permissions/:id
     */
    public function read()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '权限ID无效',
                    'error' => 'INVALID_PERMISSION_ID',
                    'error_code' => 400
                ], 400);
            }

            $permission = Permission::with(['menu:id,name,title'])
                ->find($id);

            if (!$permission) {
                return json([
                    'success' => false,
                    'message' => '权限不存在',
                    'error' => 'PERMISSION_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            return json([
                'success' => true,
                'message' => '获取权限详情成功',
                'data' => $permission->toArray()
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取权限详情失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 创建权限
     *
     * @return \think\response\Json
     *
     * 请求方式: POST
     * 请求路径: /api/permissions
     * 请求参数:
     *   - menu_id: int (可选) 关联的菜单ID
     *   - name: string (必填) 权限名称
     *   - code: string (必填) 权限代码
     *   - type: string (必填) 类型（api/menu/button）
     *   - description: string (可选) 权限描述
     *   - sort: int (可选) 排序
     */
    public function save()
    {
        try {
            $data = Request::only(['menu_id', 'name', 'code', 'type', 'description', 'sort', 'status'], 'post');

            if (empty($data['name'])) {
                return json([
                    'success' => false,
                    'message' => '权限名称不能为空',
                    'error' => 'NAME_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($data['code'])) {
                return json([
                    'success' => false,
                    'message' => '权限代码不能为空',
                    'error' => 'CODE_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($data['type'])) {
                $data['type'] = 'api';
            }

            // 检查权限代码是否已存在
            if (Permission::where('code', $data['code'])->find()) {
                return json([
                    'success' => false,
                    'message' => '权限代码已存在',
                    'error' => 'CODE_EXISTS',
                    'error_code' => 400
                ], 400);
            }

            // 验证菜单ID是否存在
            if (!empty($data['menu_id'])) {
                if (!Menu::find($data['menu_id'])) {
                    return json([
                        'success' => false,
                        'message' => '菜单不存在',
                        'error' => 'MENU_NOT_FOUND',
                        'error_code' => 400
                    ], 400);
                }
            }

            // 设置默认值
            $data['sort'] = $data['sort'] ?? 0;
            $data['status'] = $data['status'] ?? 1;

            $permission = Permission::create($data);

            return json([
                'success' => true,
                'message' => '创建权限成功',
                'data' => [
                    'id' => $permission->id,
                    'name' => $permission->name
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '创建权限失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 更新权限信息
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/permissions/:id
     */
    public function update()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '权限ID无效',
                    'error' => 'INVALID_PERMISSION_ID',
                    'error_code' => 400
                ], 400);
            }

            $data = Request::only(['menu_id', 'name', 'description', 'sort', 'status'], 'put');

            $permission = Permission::find($id);
            if (!$permission) {
                return json([
                    'success' => false,
                    'message' => '权限不存在',
                    'error' => 'PERMISSION_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 验证菜单ID是否存在
            if (isset($data['menu_id']) && !empty($data['menu_id'])) {
                if (!Menu::find($data['menu_id'])) {
                    return json([
                        'success' => false,
                        'message' => '菜单不存在',
                        'error' => 'MENU_NOT_FOUND',
                        'error_code' => 400
                    ], 400);
                }
            }

            $permission->save($data);

            return json([
                'success' => true,
                'message' => '更新权限成功',
                'data' => [
                    'id' => $permission->id,
                    'name' => $permission->name
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '更新权限失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 删除权限
     *
     * @return \think\response\Json
     *
     * 请求方式: DELETE
     * 请求路径: /api/permissions/:id
     */
    public function delete()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '权限ID无效',
                    'error' => 'INVALID_PERMISSION_ID',
                    'error_code' => 400
                ], 400);
            }

            $permission = Permission::find($id);
            if (!$permission) {
                return json([
                    'success' => false,
                    'message' => '权限不存在',
                    'error' => 'PERMISSION_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $permission->delete();

            return json([
                'success' => true,
                'message' => '删除权限成功'
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '删除权限失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }
}
