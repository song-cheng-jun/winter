<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 菜单模型
 * 负责菜单相关的数据库操作（支持无限级树形结构）
 */
class Menu extends Model
{
    // 表名
    protected $name = 'menus';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 创建时间字段名
    const CREATED_AT = 'created_at';

    // 更新时间字段名
    const UPDATED_AT = 'updated_at';

    /**
     * 关联父级菜单（多对一）
     */
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * 关联子级菜单（一对多）
     */
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->order('sort', 'asc');
    }

    /**
     * 关联角色（多对多）
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_menus', 'role_id', 'menu_id');
    }

    /**
     * 关联权限（一对多）
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'menu_id');
    }

    /**
     * 获取菜单树形结构
     *
     * @param int $parentId 父级菜单ID（默认0为顶级）
     * @param array $menuIds 菜单ID白名单（可选，用于权限过滤）
     * @return array 树形菜单数组
     */
    public static function getTree(int $parentId = 0, array $menuIds = []): array
    {
        $query = self::where('parent_id', $parentId)
            ->where('status', 1)
            ->order('sort', 'asc');

        // 如果指定了菜单ID白名单，则过滤
        if (!empty($menuIds)) {
            $query->whereIn('id', $menuIds);
        }

        $menus = $query->select()->toArray();

        foreach ($menus as &$menu) {
            // 递归获取子菜单
            $children = self::getTree($menu['id'], $menuIds);
            if (!empty($children)) {
                $menu['children'] = $children;
            }
        }

        return $menus;
    }

    /**
     * 获取所有子菜单ID（包括自身）
     *
     * @param int $menuId 菜单ID
     * @return array 菜单ID数组
     */
    public static function getChildrenIds(int $menuId): array
    {
        $ids = [$menuId];
        $children = self::where('parent_id', $menuId)->column('id');

        foreach ($children as $childId) {
            $ids = array_merge($ids, self::getChildrenIds($childId));
        }

        return $ids;
    }

    /**
     * 检查菜单是否有子菜单
     *
     * @param int $menuId 菜单ID
     * @return bool
     */
    public static function hasChildren(int $menuId): bool
    {
        return self::where('parent_id', $menuId)->count() > 0;
    }
}
