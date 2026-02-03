<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 角色菜单关联模型
 * 负责角色与菜单的多对多关联
 */
class RoleMenu extends Model
{
    // 表名
    protected $name = 'role_menus';

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
     * 关联菜单（多对一）
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * 为角色分配菜单
     *
     * @param int $roleId 角色ID
     * @param array $menuIds 菜单ID数组
     * @return bool
     */
    public static function assignMenus(int $roleId, array $menuIds): bool
    {
        // 先删除角色现有菜单
        self::where('role_id', $roleId)->delete();

        // 批量插入新菜单
        if (empty($menuIds)) {
            return true;
        }

        $data = [];
        foreach ($menuIds as $menuId) {
            $data[] = [
                'role_id' => $roleId,
                'menu_id' => $menuId,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        return self::insertAll($data) !== false;
    }

    /**
     * 获取角色的所有菜单ID
     *
     * @param int $roleId 角色ID
     * @return array 菜单ID数组
     */
    public static function getRoleMenuIds(int $roleId): array
    {
        return self::where('role_id', $roleId)->column('menu_id');
    }
}
