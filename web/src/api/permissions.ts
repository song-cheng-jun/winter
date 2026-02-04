/**
 * 权限管理 API
 *
 * 包含权限 CRUD、分组查询等接口
 */

import { request } from '@/utils/request'
import type {
  ApiResponse,
  Permission,
  PermissionListResponse,
  PermissionGroupResponse,
  PermissionForm,
  PermissionListQuery,
} from '@/types'

/**
 * 获取权限列表
 * @param params 查询参数
 */
export const getPermissionList = (params: PermissionListQuery) => {
  return request.get<PermissionListResponse>('/api/permissions', { params })
}

/**
 * 获取权限分组列表
 */
export const getPermissionGroup = () => {
  return request.get<PermissionGroupResponse>('/api/permissions/group')
}

/**
 * 获取权限详情
 * @param id 权限ID
 */
export const getPermissionDetail = (id: number) => {
  return request.get<Permission>(`/api/permissions/${id}`)
}

/**
 * 创建权限
 * @param data 权限表单数据
 */
export const createPermission = (data: PermissionForm) => {
  return request.post<{ id: number; name: string }>('/api/permissions', data)
}

/**
 * 更新权限信息
 * @param id 权限ID
 * @param data 权限表单数据
 */
export const updatePermission = (id: number, data: Partial<PermissionForm>) => {
  return request.put<{ id: number; name: string }>(`/api/permissions/${id}`, data)
}

/**
 * 删除权限
 * @param id 权限ID
 */
export const deletePermission = (id: number) => {
  return request.delete(`/api/permissions/${id}`)
}
