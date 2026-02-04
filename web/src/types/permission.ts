/**
 * 权限系统相关类型定义
 */

/**
 * 角色信息
 */
export interface Role {
  id: number
  name: string
  code: string
  description?: string
  sort: number
  status: number
  created_at: string
  updated_at: string
}

/**
 * 菜单信息
 */
export interface Menu {
  id: number
  parent_id: number
  name: string
  type: 'directory' | 'menu' | 'button'
  path?: string
  component?: string
  redirect?: string
  icon?: string
  title: string
  hidden: number
  always_show: number
  breadcrumb?: number
  affix?: number
  no_cache?: number
  sort: number
  status: number
  created_at?: string
  updated_at?: string
  children?: Menu[]
}

/**
 * 权限信息
 */
export interface Permission {
  id: number
  menu_id: number
  name: string
  code: string
  type: 'api' | 'menu' | 'button'
  description?: string
  sort: number
  status: number
  created_at?: string
  updated_at?: string
  menu?: {
    id: number
    name: string
    title: string
  }
}

/**
 * 用户角色关联
 */
export interface UserRole {
  id: number
  user_id: number
  role_id: number
  role?: Role
}

/**
 * 用户完整信息（包含角色、菜单、权限）
 */
export interface UserCompleteInfo {
  user: {
    id: number
    username: string
    nickname: string
    email?: string
    phone?: string
    avatar?: string
    status: number
  }
  roles: Role[]
  menus: Menu[]
  permissions: string[]
}

/**
 * 用户列表响应
 */
export interface UserListResponse {
  list: UserInfo[]
  total: number
  page: number
  limit: number
}

/**
 * 用户信息（扩展）
 */
export interface UserInfo {
  id: number
  username: string
  nickname: string
  avatar?: string
  email?: string
  phone?: string
  status: number
  last_login_time?: string
  last_login_ip?: string
  created_at: string
  updated_at: string
  roles?: Role[]
}

/**
 * 角色列表响应
 */
export interface RoleListResponse {
  list: Role[]
  total: number
  page: number
  limit: number
}

/**
 * 菜单列表响应（树形）
 */
export interface MenuTreeResponse {
  list: Menu[]
}

/**
 * 权限列表响应
 */
export interface PermissionListResponse {
  list: Permission[]
  total: number
  page: number
  limit: number
}

/**
 * 权限分组响应
 */
export interface PermissionGroupResponse {
  [groupName: string]: Permission[]
}

/**
 * 用户表单数据
 */
export interface UserForm {
  username?: string
  password?: string
  nickname?: string
  email?: string
  phone?: string
}

/**
 * 角色表单数据
 */
export interface RoleForm {
  name: string
  code: string
  description?: string
  sort?: number
}

/**
 * 菜单表单数据
 */
export interface MenuForm {
  parent_id?: number
  name: string
  type: 'directory' | 'menu' | 'button'
  path?: string
  component?: string
  redirect?: string
  icon?: string
  title: string
  hidden?: number
  always_show?: number
  sort?: number
}

/**
 * 权限表单数据
 */
export interface PermissionForm {
  menu_id?: number
  name: string
  code: string
  type: 'api' | 'menu' | 'button'
  description?: string
  sort?: number
}

/**
 * 列表查询参数
 */
export interface ListQuery {
  page?: number
  limit?: number
  keyword?: string
  status?: number
}

/**
 * 用户列表查询参数
 */
export interface UserListQuery extends ListQuery {
  role_id?: number
}

/**
 * 权限列表查询参数
 */
export interface PermissionListQuery extends ListQuery {
  type?: 'api' | 'menu' | 'button'
  menu_id?: number
}

/**
 * 菜单列表查询参数
 */
export interface MenuListQuery {
  parent_id?: number
  status?: number
}
