# 权限功能模块 API 接口文档

## 基本信息

**Base URL**: `http://lib.com/api`

**认证方式**: JWT Bearer Token

**请求头格式**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**响应格式**:
```json
{
  "success": true,
  "message": "操作成功",
  "data": {}
}
```

---

## 目录

1. [认证相关接口](#认证相关接口)
2. [用户管理接口](#用户管理接口)
3. [角色管理接口](#角色管理接口)
4. [菜单管理接口](#菜单管理接口)
5. [权限管理接口](#权限管理接口)

---

## 认证相关接口

### 1. 用户登录

**接口描述**: 用户登录获取 Token

**请求方式**: `POST`

**请求路径**: `/api/auth/login`

**是否需要登录**: 否

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名 |
| password | string | 是 | 密码 |

**请求示例**:
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "登录成功",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "userInfo": {
      "id": 1,
      "username": "admin",
      "nickname": "超级管理员",
      "avatar": null,
      "email": null,
      "phone": null,
      "status": 1,
      "last_login_time": "2026-02-03 23:00:00",
      "last_login_ip": "127.0.0.1",
      "created_at": "2026-01-01 00:00:00",
      "updated_at": "2026-02-03 23:00:00"
    }
  }
}
```

---

### 2. 获取用户信息

**接口描述**: 获取当前登录用户的基本信息

**请求方式**: `GET`

**请求路径**: `/api/auth/userinfo`

**是否需要登录**: 是

**请求参数**: 无

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户信息成功",
  "data": {
    "id": 1,
    "username": "admin",
    "nickname": "超级管理员",
    "avatar": null,
    "email": null,
    "phone": null,
    "status": 1,
    "last_login_time": "2026-02-03 23:00:00",
    "last_login_ip": "127.0.0.1",
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-02-03 23:00:00"
  }
}
```

---

### 3. 获取用户菜单树

**接口描述**: 获取当前登录用户的菜单树（根据角色权限过滤）

**请求方式**: `GET`

**请求路径**: `/api/auth/menus`

**是否需要登录**: 是

**请求参数**: 无

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户菜单成功",
  "data": [
    {
      "id": 1,
      "parent_id": 0,
      "name": "system",
      "type": "directory",
      "path": "/system",
      "component": null,
      "redirect": "/system/user",
      "icon": "setting",
      "title": "系统管理",
      "hidden": 0,
      "always_show": 0,
      "breadcrumb": 1,
      "affix": 0,
      "no_cache": 0,
      "sort": 1,
      "status": 1,
      "children": [
        {
          "id": 3,
          "parent_id": 1,
          "name": "user",
          "type": "menu",
          "path": "/system/user",
          "component": "system/user/index",
          "title": "用户管理",
          "icon": "user",
          "hidden": 0,
          "children": []
        },
        {
          "id": 4,
          "parent_id": 1,
          "name": "role",
          "type": "menu",
          "path": "/system/role",
          "component": "system/role/index",
          "title": "角色管理",
          "icon": "team",
          "hidden": 0,
          "children": []
        }
      ]
    }
  ]
}
```

---

### 4. 获取用户权限列表

**接口描述**: 获取当前登录用户的所有权限代码

**请求方式**: `GET`

**请求路径**: `/api/auth/permissions`

**是否需要登录**: 是

**请求参数**: 无

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户权限成功",
  "data": [
    "user:list",
    "user:create",
    "user:update",
    "user:delete",
    "role:list",
    "role:create",
    "menu:list",
    "menu:create",
    "permission:list"
  ]
}
```

---

### 5. 获取用户完整信息

**接口描述**: 获取当前登录用户的完整信息（包含角色、菜单、权限）

**请求方式**: `GET`

**请求路径**: `/api/auth/info`

**是否需要登录**: 是

**请求参数**: 无

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户信息成功",
  "data": {
    "user": {
      "id": 1,
      "username": "admin",
      "nickname": "超级管理员",
      "email": null,
      "phone": null,
      "status": 1
    },
    "roles": [
      {
        "id": 1,
        "name": "超级管理员",
        "code": "super_admin",
        "description": "拥有系统所有权限"
      }
    ],
    "menus": [],
    "permissions": [
      "user:list",
      "user:create",
      "user:update",
      "user:delete"
    ]
  }
}
```

---

### 6. 用户登出

**接口描述**: 用户登出（JWT 无状态，实际登出在前端删除 Token）

**请求方式**: `POST`

**请求路径**: `/api/auth/logout`

**是否需要登录**: 是

**请求参数**: 无

**响应示例**:
```json
{
  "success": true,
  "message": "登出成功"
}
```

---

## 用户管理接口

### 1. 获取用户列表

**接口描述**: 获取用户列表（支持分页、搜索、筛选）

**请求方式**: `GET`

**请求路径**: `/api/users`

**是否需要登录**: 是

**权限要求**: `user:list`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| limit | int | 否 | 每页数量，默认20 |
| keyword | string | 否 | 搜索关键词（用户名/昵称/邮箱/手机号） |
| status | int | 否 | 状态筛选（1正常 0禁用） |
| role_id | int | 否 | 角色筛选 |

**请求示例**:
```
GET /api/users?page=1&limit=20&keyword=admin&status=1
```

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户列表成功",
  "data": {
    "list": [
      {
        "id": 1,
        "username": "admin",
        "nickname": "超级管理员",
        "avatar": null,
        "email": "admin@example.com",
        "phone": "13800138000",
        "status": 1,
        "last_login_time": "2026-02-03 23:00:00",
        "last_login_ip": "127.0.0.1",
        "created_at": "2026-01-01 00:00:00",
        "updated_at": "2026-02-03 23:00:00",
        "roles": [
          {
            "id": 1,
            "name": "超级管理员",
            "code": "super_admin"
          }
        ]
      }
    ],
    "total": 100,
    "page": 1,
    "limit": 20
  }
}
```

---

### 2. 获取用户详情

**接口描述**: 获取指定用户的详细信息

**请求方式**: `GET`

**请求路径**: `/api/users/:id`

**是否需要登录**: 是

**权限要求**: `user:detail`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户详情成功",
  "data": {
    "id": 1,
    "username": "admin",
    "nickname": "超级管理员",
    "avatar": null,
    "email": "admin@example.com",
    "phone": "13800138000",
    "status": 1,
    "roles": [
      {
        "id": 1,
        "name": "超级管理员",
        "code": "super_admin",
        "description": "拥有系统所有权限"
      }
    ],
    "last_login_time": "2026-02-03 23:00:00",
    "last_login_ip": "127.0.0.1",
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-02-03 23:00:00"
  }
}
```

---

### 3. 创建用户

**接口描述**: 创建新用户

**请求方式**: `POST`

**请求路径**: `/api/users`

**是否需要登录**: 是

**权限要求**: `user:create`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| username | string | 是 | 用户名 |
| password | string | 是 | 密码 |
| nickname | string | 否 | 昵称 |
| email | string | 否 | 邮箱 |
| phone | string | 否 | 手机号 |

**请求示例**:
```json
{
  "username": "testuser",
  "password": "123456",
  "nickname": "测试用户",
  "email": "test@example.com",
  "phone": "13900139000"
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "创建用户成功",
  "data": {
    "id": 2,
    "username": "testuser"
  }
}
```

---

### 4. 更新用户信息

**接口描述**: 更新指定用户的基本信息

**请求方式**: `PUT`

**请求路径**: `/api/users/:id`

**是否需要登录**: 是

**权限要求**: `user:update`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| nickname | string | 否 | 昵称 |
| avatar | string | 否 | 头像URL |
| email | string | 否 | 邮箱 |
| phone | string | 否 | 手机号 |

**请求示例**:
```json
{
  "nickname": "新昵称",
  "email": "newemail@example.com"
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "更新用户成功",
  "data": {
    "id": 2,
    "username": "testuser"
  }
}
```

---

### 5. 删除用户

**接口描述**: 删除指定用户

**请求方式**: `DELETE`

**请求路径**: `/api/users/:id`

**是否需要登录**: 是

**权限要求**: `user:delete`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**响应示例**:
```json
{
  "success": true,
  "message": "删除用户成功"
}
```

---

### 6. 获取用户的角色列表

**接口描述**: 获取指定用户的角色列表

**请求方式**: `GET`

**请求路径**: `/api/users/:id/roles`

**是否需要登录**: 是

**权限要求**: `user:list-roles`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**响应示例**:
```json
{
  "success": true,
  "message": "获取用户角色成功",
  "data": [
    {
      "id": 1,
      "name": "超级管理员",
      "code": "super_admin",
      "description": "拥有系统所有权限",
      "status": 1
    }
  ]
}
```

---

### 7. 为用户分配角色

**接口描述**: 为指定用户分配角色

**请求方式**: `PUT`

**请求路径**: `/api/users/:id/roles`

**是否需要登录**: 是

**权限要求**: `user:assign-roles`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| role_ids | array | 是 | 角色ID数组 |

**请求示例**:
```json
{
  "role_ids": [1, 2]
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "角色分配成功",
  "data": {
    "user_id": 2,
    "role_ids": [1, 2]
  }
}
```

---

### 8. 修改用户状态

**接口描述**: 修改指定用户的状态（启用/禁用）

**请求方式**: `PUT`

**请求路径**: `/api/users/:id/status`

**是否需要登录**: 是

**权限要求**: `user:update-status`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| status | int | 是 | 状态：1正常 0禁用 |

**请求示例**:
```json
{
  "status": 0
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "修改用户状态成功",
  "data": {
    "id": 2,
    "status": 0
  }
}
```

---

### 9. 重置用户密码

**接口描述**: 重置指定用户的密码

**请求方式**: `PUT`

**请求路径**: `/api/users/:id/password`

**是否需要登录**: 是

**权限要求**: `user:reset-password`

**路径参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| id | int | 是 | 用户ID |

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| password | string | 是 | 新密码（至少6位） |

**请求示例**:
```json
{
  "password": "newpassword123"
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "重置密码成功",
  "data": {
    "id": 2,
    "username": "testuser"
  }
}
```

---

## 角色管理接口

### 1. 获取角色列表

**接口描述**: 获取角色列表（支持分页、搜索、筛选）

**请求方式**: `GET`

**请求路径**: `/api/roles`

**是否需要登录**: 是

**权限要求**: `role:list`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| limit | int | 否 | 每页数量，默认20 |
| keyword | string | 否 | 搜索关键词 |
| status | int | 否 | 状态筛选（1启用 0禁用） |

**响应示例**:
```json
{
  "success": true,
  "message": "获取角色列表成功",
  "data": {
    "list": [
      {
        "id": 1,
        "name": "超级管理员",
        "code": "super_admin",
        "description": "拥有系统所有权限",
        "sort": 1,
        "status": 1,
        "created_at": "2026-01-01 00:00:00",
        "updated_at": "2026-01-01 00:00:00"
      }
    ],
    "total": 3,
    "page": 1,
    "limit": 20
  }
}
```

---

### 2. 获取角色详情

**接口描述**: 获取指定角色的详细信息

**请求方式**: `GET`

**请求路径**: `/api/roles/:id`

**是否需要登录**: 是

**权限要求**: `role:detail`

**响应示例**:
```json
{
  "success": true,
  "message": "获取角色详情成功",
  "data": {
    "id": 1,
    "name": "超级管理员",
    "code": "super_admin",
    "description": "拥有系统所有权限",
    "sort": 1,
    "status": 1,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

---

### 3. 创建角色

**接口描述**: 创建新角色

**请求方式**: `POST`

**请求路径**: `/api/roles`

**是否需要登录**: 是

**权限要求**: `role:create`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| name | string | 是 | 角色名称 |
| code | string | 是 | 角色代码（唯一标识） |
| description | string | 否 | 角色描述 |
| sort | int | 否 | 排序 |

**请求示例**:
```json
{
  "name": "编辑",
  "code": "editor",
  "description": "内容编辑权限",
  "sort": 5
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "创建角色成功",
  "data": {
    "id": 4,
    "name": "编辑"
  }
}
```

---

### 4. 更新角色信息

**接口描述**: 更新指定角色的信息

**请求方式**: `PUT`

**请求路径**: `/api/roles/:id`

**是否需要登录**: 是

**权限要求**: `role:update`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| name | string | 否 | 角色名称 |
| description | string | 否 | 角色描述 |
| sort | int | 否 | 排序 |
| status | int | 否 | 状态：1启用 0禁用 |

**响应示例**:
```json
{
  "success": true,
  "message": "更新角色成功",
  "data": {
    "id": 4,
    "name": "编辑"
  }
}
```

---

### 5. 删除角色

**接口描述**: 删除指定角色

**请求方式**: `DELETE`

**请求路径**: `/api/roles/:id`

**是否需要登录**: 是

**权限要求**: `role:delete`

**响应示例**:
```json
{
  "success": true,
  "message": "删除角色成功"
}
```

---

### 6. 获取角色的权限列表

**接口描述**: 获取指定角色的所有权限

**请求方式**: `GET`

**请求路径**: `/api/roles/:id/permissions`

**是否需要登录**: 是

**权限要求**: `role:list-permissions`

**响应示例**:
```json
{
  "success": true,
  "message": "获取角色权限成功",
  "data": [
    {
      "id": 1,
      "menu_id": 3,
      "name": "查看用户列表",
      "code": "user:list",
      "type": "api",
      "description": "查看用户列表",
      "sort": 1,
      "status": 1
    }
  ]
}
```

---

### 7. 为角色分配权限

**接口描述**: 为指定角色分配权限

**请求方式**: `PUT`

**请求路径**: `/api/roles/:id/permissions`

**是否需要登录**: 是

**权限要求**: `role:assign-permissions`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| permission_ids | array | 是 | 权限ID数组 |

**请求示例**:
```json
{
  "permission_ids": [1, 2, 3, 4, 5]
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "分配权限成功",
  "data": {
    "role_id": 2,
    "permission_ids": [1, 2, 3, 4, 5]
  }
}
```

---

### 8. 获取角色的菜单列表

**接口描述**: 获取指定角色的所有菜单

**请求方式**: `GET`

**请求路径**: `/api/roles/:id/menus`

**是否需要登录**: 是

**权限要求**: `role:list-menus`

**响应示例**:
```json
{
  "success": true,
  "message": "获取角色菜单成功",
  "data": [
    {
      "id": 1,
      "parent_id": 0,
      "name": "system",
      "type": "directory",
      "path": "/system",
      "title": "系统管理",
      "icon": "setting"
    }
  ]
}
```

---

### 9. 为角色分配菜单

**接口描述**: 为指定角色分配菜单

**请求方式**: `PUT`

**请求路径**: `/api/roles/:id/menus`

**是否需要登录**: 是

**权限要求**: `role:assign-menus`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| menu_ids | array | 是 | 菜单ID数组 |

**请求示例**:
```json
{
  "menu_ids": [1, 2, 3, 4, 5]
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "分配菜单成功",
  "data": {
    "role_id": 2,
    "menu_ids": [1, 2, 3, 4, 5]
  }
}
```

---

### 10. 获取拥有该角色的用户列表

**接口描述**: 获取拥有指定角色的所有用户

**请求方式**: `GET`

**请求路径**: `/api/roles/:id/users`

**是否需要登录**: 是

**权限要求**: `role:list-users`

**响应示例**:
```json
{
  "success": true,
  "message": "获取角色用户成功",
  "data": [
    {
      "id": 1,
      "username": "admin",
      "nickname": "超级管理员",
      "avatar": null,
      "email": null,
      "phone": null,
      "status": 1
    }
  ]
}
```

---

## 菜单管理接口

### 1. 获取菜单列表（树形结构）

**接口描述**: 获取菜单树形结构

**请求方式**: `GET`

**请求路径**: `/api/menus`

**是否需要登录**: 是

**权限要求**: `menu:list`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| parent_id | int | 否 | 父级菜单ID，默认0 |
| status | int | 否 | 状态筛选（1启用 0禁用） |

**响应示例**:
```json
{
  "success": true,
  "message": "获取菜单列表成功",
  "data": [
    {
      "id": 1,
      "parent_id": 0,
      "name": "system",
      "type": "directory",
      "path": "/system",
      "redirect": "/system/user",
      "icon": "setting",
      "title": "系统管理",
      "hidden": 0,
      "always_show": 0,
      "children": [
        {
          "id": 3,
          "parent_id": 1,
          "name": "user",
          "type": "menu",
          "path": "/system/user",
          "component": "system/user/index",
          "title": "用户管理",
          "icon": "user",
          "children": []
        }
      ]
    }
  ]
}
```

---

### 2. 获取菜单树（用于下拉选择）

**接口描述**: 获取菜单树（简化版，用于下拉选择）

**请求方式**: `GET`

**请求路径**: `/api/menus/tree`

**是否需要登录**: 是

**权限要求**: `menu:tree`

**响应示例**:
```json
{
  "success": true,
  "message": "获取菜单树成功",
  "data": [
    {
      "id": 1,
      "parent_id": 0,
      "name": "system",
      "title": "系统管理",
      "children": [
        {
          "id": 3,
          "parent_id": 1,
          "name": "user",
          "title": "用户管理"
        }
      ]
    }
  ]
}
```

---

### 3. 获取菜单详情

**接口描述**: 获取指定菜单的详细信息

**请求方式**: `GET`

**请求路径**: `/api/menus/:id`

**是否需要登录**: 是

**权限要求**: `menu:detail`

**响应示例**:
```json
{
  "success": true,
  "message": "获取菜单详情成功",
  "data": {
    "id": 3,
    "parent_id": 1,
    "name": "user",
    "type": "menu",
    "path": "/system/user",
    "component": "system/user/index",
    "redirect": null,
    "icon": "user",
    "title": "用户管理",
    "hidden": 0,
    "always_show": 0,
    "breadcrumb": 1,
    "affix": 0,
    "no_cache": 0,
    "sort": 1,
    "status": 1,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00"
  }
}
```

---

### 4. 创建菜单

**接口描述**: 创建新菜单

**请求方式**: `POST`

**请求路径**: `/api/menus`

**是否需要登录**: 是

**权限要求**: `menu:create`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| parent_id | int | 否 | 父级菜单ID，默认0 |
| name | string | 是 | 菜单名称（路由名称） |
| type | string | 是 | 类型：directory/menu/button |
| path | string | 否 | 路由路径 |
| component | string | 否 | 组件路径 |
| redirect | string | 否 | 重定向路径 |
| icon | string | 否 | 图标 |
| title | string | 是 | 菜单标题（显示名称） |
| hidden | int | 否 | 是否隐藏：0否 1是 |
| always_show | int | 否 | 是否总是显示：0否 1是 |
| sort | int | 否 | 排序 |

**请求示例**:
```json
{
  "parent_id": 1,
  "name": "user",
  "type": "menu",
  "path": "/system/user",
  "component": "system/user/index",
  "title": "用户管理",
  "icon": "user",
  "sort": 1
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "创建菜单成功",
  "data": {
    "id": 7,
    "name": "user"
  }
}
```

---

### 5. 更新菜单信息

**接口描述**: 更新指定菜单的信息

**请求方式**: `PUT`

**请求路径**: `/api/menus/:id`

**是否需要登录**: 是

**权限要求**: `menu:update`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| parent_id | int | 否 | 父级菜单ID |
| name | string | 否 | 菜单名称 |
| type | string | 否 | 类型 |
| path | string | 否 | 路由路径 |
| component | string | 否 | 组件路径 |
| title | string | 否 | 菜单标题 |
| icon | string | 否 | 图标 |
| sort | int | 否 | 排序 |
| status | int | 否 | 状态 |

**响应示例**:
```json
{
  "success": true,
  "message": "更新菜单成功",
  "data": {
    "id": 7,
    "name": "user"
  }
}
```

---

### 6. 删除菜单

**接口描述**: 删除指定菜单

**请求方式**: `DELETE`

**请求路径**: `/api/menus/:id`

**是否需要登录**: 是

**权限要求**: `menu:delete`

**响应示例**:
```json
{
  "success": true,
  "message": "删除菜单成功"
}
```

---

## 权限管理接口

### 1. 获取权限列表

**接口描述**: 获取权限列表（支持分页、搜索、筛选）

**请求方式**: `GET`

**请求路径**: `/api/permissions`

**是否需要登录**: 是

**权限要求**: `permission:list`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| page | int | 否 | 页码，默认1 |
| limit | int | 否 | 每页数量，默认20 |
| keyword | string | 否 | 搜索关键词 |
| type | string | 否 | 类型筛选（api/menu/button） |
| menu_id | int | 否 | 菜单ID筛选 |

**响应示例**:
```json
{
  "success": true,
  "message": "获取权限列表成功",
  "data": {
    "list": [
      {
        "id": 1,
        "menu_id": 3,
        "name": "查看用户列表",
        "code": "user:list",
        "type": "api",
        "description": "查看用户列表",
        "sort": 1,
        "status": 1,
        "created_at": "2026-01-01 00:00:00",
        "updated_at": "2026-01-01 00:00:00"
      }
    ],
    "total": 28,
    "page": 1,
    "limit": 20
  }
}
```

---

### 2. 获取权限分组列表

**接口描述**: 获取按菜单分组的权限列表

**请求方式**: `GET`

**请求路径**: `/api/permissions/group`

**是否需要登录**: 是

**权限要求**: `permission:group`

**响应示例**:
```json
{
  "success": true,
  "message": "获取权限分组成功",
  "data": {
    "用户管理": [
      {
        "id": 1,
        "menu_id": 3,
        "name": "查看用户列表",
        "code": "user:list",
        "type": "api"
      },
      {
        "id": 2,
        "menu_id": 3,
        "name": "创建用户",
        "code": "user:create",
        "type": "api"
      }
    ],
    "角色管理": [
      {
        "id": 9,
        "menu_id": 4,
        "name": "查看角色列表",
        "code": "role:list",
        "type": "api"
      }
    ]
  }
}
```

---

### 3. 获取权限详情

**接口描述**: 获取指定权限的详细信息

**请求方式**: `GET`

**请求路径**: `/api/permissions/:id`

**是否需要登录**: 是

**权限要求**: `permission:detail`

**响应示例**:
```json
{
  "success": true,
  "message": "获取权限详情成功",
  "data": {
    "id": 1,
    "menu_id": 3,
    "name": "查看用户列表",
    "code": "user:list",
    "type": "api",
    "description": "查看用户列表",
    "sort": 1,
    "status": 1,
    "created_at": "2026-01-01 00:00:00",
    "updated_at": "2026-01-01 00:00:00",
    "menu": {
      "id": 3,
      "name": "user",
      "title": "用户管理"
    }
  }
}
```

---

### 4. 创建权限

**接口描述**: 创建新权限

**请求方式**: `POST`

**请求路径**: `/api/permissions`

**是否需要登录**: 是

**权限要求**: `permission:create`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| menu_id | int | 否 | 关联的菜单ID |
| name | string | 是 | 权限名称 |
| code | string | 是 | 权限代码（唯一标识） |
| type | string | 是 | 类型：api/menu/button |
| description | string | 否 | 权限描述 |
| sort | int | 否 | 排序 |

**请求示例**:
```json
{
  "menu_id": 3,
  "name": "导出用户",
  "code": "user:export",
  "type": "api",
  "description": "导出用户数据",
  "sort": 9
}
```

**响应示例**:
```json
{
  "success": true,
  "message": "创建权限成功",
  "data": {
    "id": 29,
    "name": "导出用户"
  }
}
```

---

### 5. 更新权限信息

**接口描述**: 更新指定权限的信息

**请求方式**: `PUT`

**请求路径**: `/api/permissions/:id`

**是否需要登录**: 是

**权限要求**: `permission:update`

**请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
|--------|------|------|------|
| menu_id | int | 否 | 关联的菜单ID |
| name | string | 否 | 权限名称 |
| description | string | 否 | 权限描述 |
| sort | int | 否 | 排序 |
| status | int | 否 | 状态 |

**响应示例**:
```json
{
  "success": true,
  "message": "更新权限成功",
  "data": {
    "id": 29,
    "name": "导出用户"
  }
}
```

---

### 6. 删除权限

**接口描述**: 删除指定权限

**请求方式**: `DELETE`

**请求路径**: `/api/permissions/:id`

**是否需要登录**: 是

**权限要求**: `permission:delete`

**响应示例**:
```json
{
  "success": true,
  "message": "删除权限成功"
}
```

---

## 错误码说明

| HTTP 状态码 | 错误代码 | 说明 |
|-------------|----------|------|
| 200 | - | 请求成功 |
| 400 | VALIDATION_ERROR | 参数验证失败 |
| 401 | UNAUTHORIZED | 未登录或 Token 无效 |
| 401 | TOKEN_EXPIRED | Token 已过期 |
| 401 | TOKEN_INVALID | Token 签名无效 |
| 403 | PERMISSION_DENIED | 无权限访问 |
| 404 | NOT_FOUND | 资源不存在 |
| 500 | SERVER_ERROR | 服务器错误 |

---

## 测试账号

| 用户名 | 密码 | 角色 | 说明 |
|--------|------|------|------|
| admin | admin123 | 超级管理员 | 拥有所有权限 |
