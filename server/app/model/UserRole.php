<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 用户角色关联模型
 * 负责用户与角色的多对多关联
 */
class UserRole extends Model
{
    // 表名
    protected $name = 'user_roles';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 创建时间字段名
    const CREATED_AT = 'created_at';

    // 禁用更新时间
    const UPDATED_AT = null;

    /**
     * 关联用户（多对一）
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 关联角色（多对一）
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * 为用户分配角色
     *
     * @param int $userId 用户ID
     * @param array $roleIds 角色ID数组
     * @return bool
     */
    public static function assignRoles(int $userId, array $roleIds): bool
    {
        // 先删除用户现有角色
        self::where('user_id', $userId)->delete();

        // 批量插入新角色
        if (empty($roleIds)) {
            return true;
        }

        $data = [];
        foreach ($roleIds as $roleId) {
            $data[] = [
                'user_id' => $userId,
                'role_id' => $roleId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        return self::insertAll($data) !== false;
    }

    /**
     * 获取用户的所有角色ID
     *
     * @param int $userId 用户ID
     * @return array 角色ID数组
     */
    public static function getUserRoleIds(int $userId): array
    {
        return self::where('user_id', $userId)->column('role_id');
    }

    /**
     * 获取用户的所有角色
     *
     * @param int $userId 用户ID
     * @return array 角色数组
     */
    public static function getUserRoles(int $userId): array
    {
        return self::where('user_id', $userId)
            ->with(['role'])
            ->select()
            ->toArray();
    }
}
