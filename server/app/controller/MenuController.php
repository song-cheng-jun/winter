<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Menu;
use think\facade\Request;

/**
 * 菜单管理控制器
 * 负责菜单的增删改查、树形结构查询等功能
 */
class MenuController extends BaseController
{
    /**
     * 获取菜单列表（树形结构）
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/menus
     * 请求参数:
     *   - parent_id: int (可选) 父级菜单ID，默认0（顶级）
     *   - status: int (可选) 状态筛选
     */
    public function index()
    {
        try {
            $parentId = input('parent_id', 0, 'int');
            $status = input('status', '', 'int');

            $query = Menu::where('parent_id', $parentId);

            if ($status !== '') {
                $query->where('status', $status);
            }

            $menus = $query->order('sort', 'asc')
                ->order('id', 'asc')
                ->select()
                ->toArray();

            // 为每个菜单添加子菜单
            foreach ($menus as &$menu) {
                $menu['children'] = $this->getChildren($menu['id'], $status);
            }

            return json([
                'success' => true,
                'message' => '获取菜单列表成功',
                'data' => $menus
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取菜单列表失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取菜单树（用于下拉选择）
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/menus/tree
     */
    public function tree()
    {
        try {
            $status = input('status', 1, 'int');

            $menus = Menu::getTree(0, [], $status);

            return json([
                'success' => true,
                'message' => '获取菜单树成功',
                'data' => $menus
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取菜单树失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 递归获取子菜单
     *
     * @param int $parentId
     * @param mixed $status
     * @return array
     */
    private function getChildren(int $parentId, $status = ''): array
    {
        $query = Menu::where('parent_id', $parentId);

        if ($status !== '') {
            $query->where('status', $status);
        }

        $children = $query->order('sort', 'asc')
            ->order('id', 'asc')
            ->select()
            ->toArray();

        foreach ($children as &$child) {
            $child['children'] = $this->getChildren($child['id'], $status);
        }

        return $children;
    }

    /**
     * 获取菜单详情
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/menus/:id
     */
    public function read()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '菜单ID无效',
                    'error' => 'INVALID_MENU_ID',
                    'error_code' => 400
                ], 400);
            }

            $menu = Menu::find($id);

            if (!$menu) {
                return json([
                    'success' => false,
                    'message' => '菜单不存在',
                    'error' => 'MENU_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            return json([
                'success' => true,
                'message' => '获取菜单详情成功',
                'data' => $menu->toArray()
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取菜单详情失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 创建菜单
     *
     * @return \think\response\Json
     *
     * 请求方式: POST
     * 请求路径: /api/menus
     * 请求参数:
     *   - parent_id: int (可选) 父级菜单ID，默认0
     *   - name: string (必填) 菜单名称
     *   - type: string (必填) 类型：directory/menu/button
     *   - path: string (可选) 路由路径
     *   - component: string (可选) 组件路径
     *   - title: string (必填) 菜单标题
     *   - icon: string (可选) 图标
     *   - sort: int (可选) 排序
     */
    public function save()
    {
        try {
            $data = Request::only([
                'parent_id', 'name', 'type', 'path', 'component',
                'redirect', 'icon', 'title', 'hidden', 'always_show',
                'breadcrumb', 'affix', 'no_cache', 'sort', 'status'
            ], 'post');

            if (empty($data['name'])) {
                return json([
                    'success' => false,
                    'message' => '菜单名称不能为空',
                    'error' => 'NAME_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($data['title'])) {
                return json([
                    'success' => false,
                    'message' => '菜单标题不能为空',
                    'error' => 'TITLE_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($data['type'])) {
                $data['type'] = 'menu';
            }

            if (!isset($data['parent_id'])) {
                $data['parent_id'] = 0;
            }

            // 设置默认值
            $data['hidden'] = $data['hidden'] ?? 0;
            $data['always_show'] = $data['always_show'] ?? 0;
            $data['breadcrumb'] = $data['breadcrumb'] ?? 1;
            $data['affix'] = $data['affix'] ?? 0;
            $data['no_cache'] = $data['no_cache'] ?? 0;
            $data['sort'] = $data['sort'] ?? 0;
            $data['status'] = $data['status'] ?? 1;

            $menu = Menu::create($data);

            return json([
                'success' => true,
                'message' => '创建菜单成功',
                'data' => [
                    'id' => $menu->id,
                    'name' => $menu->name
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '创建菜单失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 更新菜单信息
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/menus/:id
     */
    public function update()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '菜单ID无效',
                    'error' => 'INVALID_MENU_ID',
                    'error_code' => 400
                ], 400);
            }

            $data = Request::only([
                'parent_id', 'name', 'type', 'path', 'component',
                'redirect', 'icon', 'title', 'hidden', 'always_show',
                'breadcrumb', 'affix', 'no_cache', 'sort', 'status'
            ], 'put');

            $menu = Menu::find($id);
            if (!$menu) {
                return json([
                    'success' => false,
                    'message' => '菜单不存在',
                    'error' => 'MENU_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 检查是否将父级设置为自身
            if (isset($data['parent_id']) && $data['parent_id'] == $id) {
                return json([
                    'success' => false,
                    'message' => '不能将父级设置为自身',
                    'error' => 'INVALID_PARENT_ID',
                    'error_code' => 400
                ], 400);
            }

            $menu->save($data);

            return json([
                'success' => true,
                'message' => '更新菜单成功',
                'data' => [
                    'id' => $menu->id,
                    'name' => $menu->name
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '更新菜单失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 删除菜单
     *
     * @return \think\response\Json
     *
     * 请求方式: DELETE
     * 请求路径: /api/menus/:id
     */
    public function delete()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '菜单ID无效',
                    'error' => 'INVALID_MENU_ID',
                    'error_code' => 400
                ], 400);
            }

            $menu = Menu::find($id);
            if (!$menu) {
                return json([
                    'success' => false,
                    'message' => '菜单不存在',
                    'error' => 'MENU_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 检查是否有子菜单
            if (Menu::hasChildren($id)) {
                return json([
                    'success' => false,
                    'message' => '该菜单下有子菜单，请先删除子菜单',
                    'error' => 'HAS_CHILDREN',
                    'error_code' => 400
                ], 400);
            }

            $menu->delete();

            return json([
                'success' => true,
                'message' => '删除菜单成功'
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '删除菜单失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }
}
