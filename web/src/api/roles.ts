/**
 * 角色管理 API
 *
 * 包含角色 CRUD、权限分配、菜单分配等接口
 */

import { request } from '@/utils/request'
import type {
  ApiResponse,
  Role,
  RoleListResponse,
  RoleForm,
  ListQuery,
  Permission,
  Menu,
  UserInfo,
} from '@/types'

/**
 * 获取角色列表
 * @param params 查询参数
 */
export const getRoleList = (params: ListQuery) => {
  return request.get<RoleListResponse>('/api/roles', { params })
}

/**
 * 获取角色详情
 * @param id 角色ID
 */
export const getRoleDetail = (id: number) => {
  return request.get<Role>(`/api/roles/${id}`)
}

/**
 * 创建角色
 * @param data 角色表单数据
 */
export const createRole = (data: RoleForm) => {
  return request.post<{ id: number; name: string }>('/api/roles', data)
}

/**
 * 更新角色信息
 * @param id 角色ID
 * @param data 角色表单数据
 */
export const updateRole = (id: number, data: Partial<RoleForm>) => {
  return request.put<{ id: number; name: string }>(`/api/roles/${id}`, data)
}

/**
 * 删除角色
 * @param id 角色ID
 */
export const deleteRole = (id: number) => {
  return request.delete(`/api/roles/${id}`)
}

/**
 * 获取角色的权限列表
 * @param id 角色ID
 */
export const getRolePermissions = (id: number) => {
  return request.get<Permission[]>(`/api/roles/${id}/permissions`)
}

/**
 * 为角色分配权限
 * @param id 角色ID
 * @param permissionIds 权限ID数组
 */
export const assignRolePermissions = (id: number, permissionIds: number[]) => {
  return request.put<{ role_id: number; permission_ids: number[] }>(
    `/api/roles/${id}/permissions`,
    { permission_ids: permissionIds }
  )
}

/**
 * 获取角色的菜单列表
 * @param id 角色ID
 */
export const getRoleMenus = (id: number) => {
  return request.get<Menu[]>(`/api/roles/${id}/menus`)
}

/**
 * 为角色分配菜单
 * @param id 角色ID
 * @param menuIds 菜单ID数组
 */
export const assignRoleMenus = (id: number, menuIds: number[]) => {
  return request.put<{ role_id: number; menu_ids: number[] }>(`/api/roles/${id}/menus`, {
    menu_ids: menuIds,
  })
}

/**
 * 获取拥有该角色的用户列表
 * @param id 角色ID
 */
export const getRoleUsers = (id: number) => {
  return request.get<UserInfo[]>(`/api/roles/${id}/users`)
}
