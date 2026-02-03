-- ==========================================
-- 权限功能模块数据库表创建脚本
-- ==========================================

-- ==========================================
-- 1. 修改用户表（移除 role 字段）
-- ==========================================
-- 先备份现有数据，然后删除 role 字段
-- ALTER TABLE `users` DROP COLUMN `role`;

-- 注意：如果需要保留现有 admin 用户的角色信息，可以先创建角色和关联数据
-- 然后再删除 role 字段

-- ==========================================
-- 2. 角色表 (roles)
-- ==========================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `code` varchar(50) NOT NULL COMMENT '角色代码（唯一标识）',
  `description` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `sort` int(11) DEFAULT 0 COMMENT '排序（数字越小越靠前）',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

-- ==========================================
-- 3. 用户-角色关联表 (user_roles)
-- ==========================================
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `role_id` int(11) unsigned NOT NULL COMMENT '角色ID',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role` (`user_id`, `role_id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表';

-- ==========================================
-- 4. 菜单表 (menus) - 支持无限级树形结构
-- ==========================================
CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `parent_id` int(11) unsigned DEFAULT 0 COMMENT '父级菜单ID（0为顶级菜单）',
  `name` varchar(50) NOT NULL COMMENT '菜单名称（路由名称）',
  `type` enum('directory','menu','button') DEFAULT 'menu' COMMENT '类型：directory目录 menu菜单 button按钮',
  `path` varchar(255) DEFAULT NULL COMMENT '路由路径（前端路由）',
  `component` varchar(255) DEFAULT NULL COMMENT '组件路径',
  `redirect` varchar(255) DEFAULT NULL COMMENT '重定向路径',
  `icon` varchar(100) DEFAULT NULL COMMENT '菜单图标',
  `title` varchar(50) DEFAULT NULL COMMENT '菜单标题（显示名称）',
  `hidden` tinyint(1) DEFAULT 0 COMMENT '是否隐藏：1是 0否',
  `always_show` tinyint(1) DEFAULT 0 COMMENT '是否总是显示：1是 0否',
  `breadcrumb` tinyint(1) DEFAULT 1 COMMENT '是否显示面包屑：1是 0否',
  `affix` tinyint(1) DEFAULT 0 COMMENT '是否固定标签：1是 0否',
  `no_cache` tinyint(1) DEFAULT 0 COMMENT '是否不缓存：1是 0否',
  `sort` int(11) DEFAULT 0 COMMENT '排序（数字越小越靠前）',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜单表';

-- ==========================================
-- 5. 权限表 (permissions)
-- ==========================================
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `menu_id` int(11) unsigned DEFAULT NULL COMMENT '关联的菜单ID（可选）',
  `name` varchar(50) NOT NULL COMMENT '权限名称',
  `code` varchar(100) NOT NULL COMMENT '权限代码（唯一标识，如：user:list, user:create）',
  `type` enum('api','menu','button') DEFAULT 'api' COMMENT '权限类型：api接口 menu菜单 button按钮',
  `description` varchar(255) DEFAULT NULL COMMENT '权限描述',
  `sort` int(11) DEFAULT 0 COMMENT '排序（数字越小越靠前）',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `menu_id` (`menu_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

-- ==========================================
-- 6. 角色-权限关联表 (role_permissions)
-- ==========================================
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `role_id` int(11) unsigned NOT NULL COMMENT '角色ID',
  `permission_id` int(11) unsigned NOT NULL COMMENT '权限ID',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`, `permission_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表';

-- ==========================================
-- 7. 角色-菜单关联表 (role_menus)
-- ==========================================
CREATE TABLE IF NOT EXISTS `role_menus` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `role_id` int(11) unsigned NOT NULL COMMENT '角色ID',
  `menu_id` int(11) unsigned NOT NULL COMMENT '菜单ID',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_menu` (`role_id`, `menu_id`),
  KEY `role_id` (`role_id`),
  KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色菜单关联表';

-- ==========================================
-- 初始化数据
-- ==========================================

-- 插入默认角色
INSERT INTO `roles` (`id`, `name`, `code`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(1, '超级管理员', 'super_admin', '拥有系统所有权限', 1, 1, NOW(), NOW()),
(2, '管理员', 'admin', '拥有大部分管理权限', 2, 1, NOW(), NOW()),
(3, '普通用户', 'user', '普通用户权限', 3, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 插入系统菜单（顶级目录）
INSERT INTO `menus` (`id`, `parent_id`, `name`, `type`, `path`, `title`, `icon`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, 'system', 'directory', '/system', '系统管理', 'setting', 1, 1, NOW(), NOW()),
(2, 0, 'dashboard', 'menu', '/dashboard', '首页', 'dashboard', 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 插入系统管理子菜单
INSERT INTO `menus` (`parent_id`, `name`, `type`, `path`, `component`, `title`, `icon`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(1, 'user', 'menu', '/system/user', 'system/user/index', '用户管理', 'user', 1, 1, NOW(), NOW()),
(1, 'role', 'menu', '/system/role', 'system/role/index', '角色管理', 'team', 2, 1, NOW(), NOW()),
(1, 'menu', 'menu', '/system/menu', 'system/menu/index', '菜单管理', 'menu', 3, 1, NOW(), NOW()),
(1, 'permission', 'menu', '/system/permission', 'system/permission/index', '权限管理', 'key', 4, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- 插入用户管理权限
INSERT INTO `permissions` (`menu_id`, `name`, `code`, `type`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(3, '查看用户列表', 'user:list', 'api', '查看用户列表', 1, 1, NOW(), NOW()),
(3, '查看用户详情', 'user:detail', 'api', '查看用户详情', 2, 1, NOW(), NOW()),
(3, '创建用户', 'user:create', 'api', '创建用户', 3, 1, NOW(), NOW()),
(3, '更新用户', 'user:update', 'api', '更新用户信息', 4, 1, NOW(), NOW()),
(3, '删除用户', 'user:delete', 'api', '删除用户', 5, 1, NOW(), NOW()),
(3, '分配角色', 'user:assign-roles', 'api', '为用户分配角色', 6, 1, NOW(), NOW()),
(3, '修改用户状态', 'user:update-status', 'api', '修改用户状态', 7, 1, NOW(), NOW()),
(3, '重置用户密码', 'user:reset-password', 'api', '重置用户密码', 8, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`);

-- 插入角色管理权限
INSERT INTO `permissions` (`menu_id`, `name`, `code`, `type`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(4, '查看角色列表', 'role:list', 'api', '查看角色列表', 1, 1, NOW(), NOW()),
(4, '查看角色详情', 'role:detail', 'api', '查看角色详情', 2, 1, NOW(), NOW()),
(4, '创建角色', 'role:create', 'api', '创建角色', 3, 1, NOW(), NOW()),
(4, '更新角色', 'role:update', 'api', '更新角色信息', 4, 1, NOW(), NOW()),
(4, '删除角色', 'role:delete', 'api', '删除角色', 5, 1, NOW(), NOW()),
(4, '分配权限', 'role:assign-permissions', 'api', '为角色分配权限', 6, 1, NOW(), NOW()),
(4, '分配菜单', 'role:assign-menus', 'api', '为角色分配菜单', 7, 1, NOW(), NOW()),
(4, '查看角色用户', 'role:list-users', 'api', '查看拥有该角色的用户列表', 8, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`);

-- 插入菜单管理权限
INSERT INTO `permissions` (`menu_id`, `name`, `code`, `type`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(5, '查看菜单列表', 'menu:list', 'api', '查看菜单列表', 1, 1, NOW(), NOW()),
(5, '查看菜单树', 'menu:tree', 'api', '查看菜单树', 2, 1, NOW(), NOW()),
(5, '查看菜单详情', 'menu:detail', 'api', '查看菜单详情', 3, 1, NOW(), NOW()),
(5, '创建菜单', 'menu:create', 'api', '创建菜单', 4, 1, NOW(), NOW()),
(5, '更新菜单', 'menu:update', 'api', '更新菜单信息', 5, 1, NOW(), NOW()),
(5, '删除菜单', 'menu:delete', 'api', '删除菜单', 6, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`);

-- 插入权限管理权限
INSERT INTO `permissions` (`menu_id`, `name`, `code`, `type`, `description`, `sort`, `status`, `created_at`, `updated_at`) VALUES
(6, '查看权限列表', 'permission:list', 'api', '查看权限列表', 1, 1, NOW(), NOW()),
(6, '查看权限分组', 'permission:group', 'api', '查看权限分组', 2, 1, NOW(), NOW()),
(6, '查看权限详情', 'permission:detail', 'api', '查看权限详情', 3, 1, NOW(), NOW()),
(6, '创建权限', 'permission:create', 'api', '创建权限', 4, 1, NOW(), NOW()),
(6, '更新权限', 'permission:update', 'api', '更新权限信息', 5, 1, NOW(), NOW()),
(6, '删除权限', 'permission:delete', 'api', '删除权限', 6, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `code`=VALUES(`code`);

-- 为超级管理员分配所有权限
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`)
SELECT 1, id, NOW() FROM `permissions`;

-- 为超级管理员分配所有菜单
INSERT IGNORE INTO `role_menus` (`role_id`, `menu_id`, `created_at`)
SELECT 1, id, NOW() FROM `menus`;

-- 为现有admin用户分配超级管理员角色
-- 假设admin用户的id是1
INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`, `created_at`)
SELECT 1, 1, NOW();

-- ==========================================
-- 完成提示
-- ==========================================
SELECT '权限功能模块数据表创建完成！' AS message;
SELECT COUNT(*) AS role_count FROM `roles`;
SELECT COUNT(*) AS menu_count FROM `menus`;
SELECT COUNT(*) AS permission_count FROM `permissions`;
