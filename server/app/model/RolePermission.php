<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 角色权限关联模型
 * 负责角色与权限的多对多关联
 */
class RolePermission extends Model
{
    // 表名
    protected $name = 'role_permissions';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 创建时间字段名
    const CREATED_AT = 'created_at';

    // 禁用更新时间
    const UPDATED_AT = null;

    /**
     * 关联角色（多对一）
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * 关联权限（多对一）
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * 为角色分配权限
     *
     * @param int $roleId 角色ID
     * @param array $permissionIds 权限ID数组
     * @return bool
     */
    public static function assignPermissions(int $roleId, array $permissionIds): bool
    {
        // 先删除角色现有权限
        self::where('role_id', $roleId)->delete();

        // 批量插入新权限
        if (empty($permissionIds)) {
            return true;
        }

        $data = [];
        foreach ($permissionIds as $permissionId) {
            $data[] = [
                'role_id' => $roleId,
                'permission_id' => $permissionId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        return self::insertAll($data) !== false;
    }

    /**
     * 获取角色的所有权限ID
     *
     * @param int $roleId 角色ID
     * @return array 权限ID数组
     */
    public static function getRolePermissionIds(int $roleId): array
    {
        return self::where('role_id', $roleId)->column('permission_id');
    }
}
