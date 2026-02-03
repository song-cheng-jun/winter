<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 权限模型
 * 负责权限相关的数据库操作
 */
class Permission extends Model
{
    // 表名
    protected $name = 'permissions';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 创建时间字段名
    const CREATED_AT = 'created_at';

    // 更新时间字段名
    const UPDATED_AT = 'updated_at';

    /**
     * 关联菜单（多对一）
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * 关联角色（多对多）
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /**
     * 根据权限代码查找权限
     *
     * @param string $code 权限代码
     * @return Permission|null 权限对象或null
     */
    public static function findByCode(string $code): ?Permission
    {
        return self::where('code', $code)->find();
    }

    /**
     * 根据类型获取权限列表
     *
     * @param string $type 权限类型（api/menu/button）
     * @return array 权限列表
     */
    public static function getByType(string $type): array
    {
        return self::where('type', $type)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取权限分组列表（按菜单分组）
     *
     * @return array 分组后的权限列表
     */
    public static function getGroupedList(): array
    {
        $permissions = self::with(['menu:id,name,title'])
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        $grouped = [];
        foreach ($permissions as $permission) {
            $menuName = $permission['menu']['title'] ?? '其他';
            if (!isset($grouped[$menuName])) {
                $grouped[$menuName] = [];
            }
            $grouped[$menuName][] = $permission;
        }

        return $grouped;
    }
}
