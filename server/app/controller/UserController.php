<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\UserRole;
use app\model\Role;
use think\facade\Request;
use think\facade\Db;

/**
 * 用户管理控制器
 * 负责用户的增删改查、角色分配等功能
 */
class UserController extends BaseController
{
    /**
     * 获取用户列表（分页）
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/users
     * 请求参数:
     *   - page: int (可选) 页码，默认1
     *   - limit: int (可选) 每页数量，默认20
     *   - keyword: string (可选) 搜索关键词
     *   - status: int (可选) 状态筛选
     *   - role_id: int (可选) 角色筛选
     *
     * 返回数据:
     *   success: true/false
     *   message: string
     *   data: object
     *     - list: array 用户列表
     *     - total: int 总数
     *     - page: int 当前页
     *     - limit: int 每页数量
     */
    public function index()
    {
        try {
            // 获取分页参数
            $page = input('page', 1, 'int');
            $limit = input('limit', 20, 'int');
            $keyword = input('keyword', '', 'trim');
            $status = input('status', '', 'int');
            $roleId = input('role_id', '', 'int');

            // 构建查询
            $query = User::field('id,username,nickname,avatar,email,phone,status,last_login_time,last_login_ip,created_at,updated_at');

            // 搜索关键词
            if (!empty($keyword)) {
                $query->whereLike('username|nickname|email|phone', '%' . $keyword . '%');
            }

            // 状态筛选
            if ($status !== '') {
                $query->where('status', $status);
            }

            // 角色筛选
            if (!empty($roleId)) {
                $query->whereExists(function($query) use ($roleId) {
                    $query->table('user_roles')->field('id')
                        ->where('user_roles.user_id = users.id')
                        ->where('user_roles.role_id', $roleId);
                });
            }

            // 获取总数
            $total = $query->count();

            // 获取列表（带角色信息）
            $list = $query->with(['roles' => function($query) {
                $query->field('id,name,code');
            }])
                ->order('id', 'desc')
                ->page($page, $limit)
                ->select()
                ->toArray();

            return json([
                'success' => true,
                'message' => '获取用户列表成功',
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
                'message' => '获取用户列表失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取用户详情
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/users/:id
     */
    public function read()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
                    'error_code' => 400
                ], 400);
            }

            $user = User::where('id', $id)
                ->with(['roles' => function($query) {
                    $query->field('id,name,code,description');
                }])
                ->field('id,username,nickname,avatar,email,phone,status,last_login_time,last_login_ip,created_at,updated_at')
                ->find();

            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            return json([
                'success' => true,
                'message' => '获取用户详情成功',
                'data' => $user->toArray()
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户详情失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 创建用户
     *
     * @return \think\response\Json
     *
     * 请求方式: POST
     * 请求路径: /api/users
     * 请求参数:
     *   - username: string (必填) 用户名
     *   - password: string (必填) 密码
     *   - nickname: string (可选) 昵称
     *   - email: string (可选) 邮箱
     *   - phone: string (可选) 手机号
     */
    public function save()
    {
        try {
            // 获取参数
            $data = Request::only(['username', 'password', 'nickname', 'email', 'phone'], 'post');

            // 参数验证
            if (empty($data['username'])) {
                return json([
                    'success' => false,
                    'message' => '用户名不能为空',
                    'error' => 'USERNAME_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            if (empty($data['password'])) {
                return json([
                    'success' => false,
                    'message' => '密码不能为空',
                    'error' => 'PASSWORD_EMPTY',
                    'error_code' => 400
                ], 400);
            }

            // 检查用户名是否已存在
            if (User::where('username', $data['username'])->find()) {
                return json([
                    'success' => false,
                    'message' => '用户名已存在',
                    'error' => 'USERNAME_EXISTS',
                    'error_code' => 400
                ], 400);
            }

            // 加密密码
            $data['password'] = User::makePassword($data['password']);

            // 创建用户
            $user = User::create($data);

            return json([
                'success' => true,
                'message' => '创建用户成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '创建用户失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 更新用户信息
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/users/:id
     */
    public function update()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
                    'error_code' => 400
                ], 400);
            }

            // 获取参数（排除密码和用户名）
            $data = Request::only(['nickname', 'avatar', 'email', 'phone'], 'put');

            // 更新用户
            $user = User::find($id);
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $user->save($data);

            return json([
                'success' => true,
                'message' => '更新用户成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '更新用户失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 删除用户
     *
     * @return \think\response\Json
     *
     * 请求方式: DELETE
     * 请求路径: /api/users/:id
     */
    public function delete()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
                    'error_code' => 400
                ], 400);
            }

            // 检查用户是否存在
            $user = User::find($id);
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 删除用户（关联关系会自动删除）
            $user->delete();

            return json([
                'success' => true,
                'message' => '删除用户成功'
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '删除用户失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 获取用户的角色列表
     *
     * @return \think\response\Json
     *
     * 请求方式: GET
     * 请求路径: /api/users/:id/roles
     */
    public function getRoles()
    {
        try {
            $id = input('id', 0, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
                    'error_code' => 400
                ], 400);
            }

            // 检查用户是否存在
            if (!User::find($id)) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 获取用户的角色
            $roles = UserRole::where('user_id', $id)
                ->with(['role' => function($query) {
                    $query->field('id,name,code,description,status');
                }])
                ->select()
                ->toArray();

            $roleList = array_column($roles, 'role');

            return json([
                'success' => true,
                'message' => '获取用户角色成功',
                'data' => $roleList
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '获取用户角色失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 为用户分配角色
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/users/:id/roles
     * 请求参数:
     *   - role_ids: array 角色ID数组
     */
    public function assignRoles()
    {
        try {
            $id = input('id', 0, 'int');
            $roleIds = input('role_ids', [], 'array');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
                    'error_code' => 400
                ], 400);
            }

            // 检查用户是否存在
            if (!User::find($id)) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 验证角色ID是否存在
            if (!empty($roleIds)) {
                $validRoleIds = Role::whereIn('id', $roleIds)->column('id');
                if (count($validRoleIds) !== count($roleIds)) {
                    return json([
                        'success' => false,
                        'message' => '存在无效的角色ID',
                        'error' => 'INVALID_ROLE_IDS',
                        'error_code' => 400
                    ], 400);
                }
            }

            // 分配角色
            UserRole::assignRoles($id, $roleIds);

            return json([
                'success' => true,
                'message' => '角色分配成功',
                'data' => [
                    'user_id' => $id,
                    'role_ids' => $roleIds
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '角色分配失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 修改用户状态
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/users/:id/status
     * 请求参数:
     *   - status: int (必填) 状态：1正常 0禁用
     */
    public function updateStatus()
    {
        try {
            $id = input('id', 0, 'int');
            $status = input('status', 1, 'int');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
                    'error_code' => 400
                ], 400);
            }

            if (!in_array($status, [0, 1])) {
                return json([
                    'success' => false,
                    'message' => '状态值无效',
                    'error' => 'INVALID_STATUS',
                    'error_code' => 400
                ], 400);
            }

            $user = User::find($id);
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            $user->status = $status;
            $user->save();

            return json([
                'success' => true,
                'message' => '修改用户状态成功',
                'data' => [
                    'id' => $id,
                    'status' => $status
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '修改用户状态失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }

    /**
     * 重置用户密码
     *
     * @return \think\response\Json
     *
     * 请求方式: PUT
     * 请求路径: /api/users/:id/password
     * 请求参数:
     *   - password: string (必填) 新密码
     */
    public function resetPassword()
    {
        try {
            $id = input('id', 0, 'int');
            $password = input('password', '', 'trim');

            if ($id <= 0) {
                return json([
                    'success' => false,
                    'message' => '用户ID无效',
                    'error' => 'INVALID_USER_ID',
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

            if (strlen($password) < 6) {
                return json([
                    'success' => false,
                    'message' => '密码长度不能少于6位',
                    'error' => 'PASSWORD_TOO_SHORT',
                    'error_code' => 400
                ], 400);
            }

            $user = User::find($id);
            if (!$user) {
                return json([
                    'success' => false,
                    'message' => '用户不存在',
                    'error' => 'USER_NOT_FOUND',
                    'error_code' => 404
                ], 404);
            }

            // 更新密码
            $user->password = User::makePassword($password);
            $user->save();

            return json([
                'success' => true,
                'message' => '重置密码成功',
                'data' => [
                    'id' => $id,
                    'username' => $user->username
                ]
            ]);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '重置密码失败',
                'error' => $e->getMessage(),
                'error_code' => 500
            ], 500);
        }
    }
}
