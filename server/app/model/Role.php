<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 角色模型
 * 负责角色相关的数据库操作
 */
class Role extends Model
{
    // 表名
    protected $name = 'roles';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 创建时间字段名
    const CREATED_AT = 'created_at';

    // 更新时间字段名
    const UPDATED_AT = 'updated_at';

    /**
     * 关联用户（多对多）
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * 关联权限（多对多）
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'permission_id', 'role_id');
    }

    /**
     * 关联菜单（多对多）
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menus', 'menu_id', 'role_id');
    }

    /**
     * 根据角色代码查找角色
     *
     * @param string $code 角色代码
     * @return Role|null 角色对象或null
     */
    public static function findByCode(string $code): ?Role
    {
        return self::where('code', $code)->find();
    }

    /**
     * 获取角色的所有权限代码
     *
     * @param int $roleId 角色ID
     * @return array 权限代码数组
     */
    public static function getPermissionCodes(int $roleId): array
    {
        $permissions = self::where('id', $roleId)
            ->permissions()
            ->where('status', 1)
            ->column('code');

        return $permissions ?: [];
    }

    /**
     * 获取角色的所有菜单ID
     *
     * @param int $roleId 角色ID
     * @return array 菜单ID数组
     */
    public static function getMenuIds(int $roleId): array
    {
        $menuIds = self::where('id', $roleId)
            ->menus()
            ->where('status', 1)
            ->column('id');

        return $menuIds ?: [];
    }
}
