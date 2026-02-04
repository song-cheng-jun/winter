/**
 * 用户管理 API
 *
 * 包含用户 CRUD、角色分配、状态管理、密码重置等接口
 */

import { request } from '@/utils/request'
import type {
  ApiResponse,
  UserInfo,
  UserListResponse,
  UserForm,
  UserListQuery,
  Role,
} from '@/types'

/**
 * 获取用户列表
 * @param params 查询参数
 */
export const getUserList = (params: UserListQuery) => {
  return request.get<UserListResponse>('/api/users', { params })
}

/**
 * 获取用户详情
 * @param id 用户ID
 */
export const getUserDetail = (id: number) => {
  return request.get<UserInfo>(`/api/users/${id}`)
}

/**
 * 创建用户
 * @param data 用户表单数据
 */
export const createUser = (data: UserForm) => {
  return request.post<{ id: number; username: string }>('/api/users', data)
}

/**
 * 更新用户信息
 * @param id 用户ID
 * @param data 用户表单数据
 */
export const updateUser = (id: number, data: Partial<UserForm>) => {
  return request.put(`/api/users/${id}`, data)
}

/**
 * 删除用户
 * @param id 用户ID
 */
export const deleteUser = (id: number) => {
  return request.delete(`/api/users/${id}`)
}

/**
 * 获取用户的角色列表
 * @param id 用户ID
 */
export const getUserRoles = (id: number) => {
  return request.get<Role[]>(`/api/users/${id}/roles`)
}

/**
 * 为用户分配角色
 * @param id 用户ID
 * @param roleIds 角色ID数组
 */
export const assignUserRoles = (id: number, roleIds: number[]) => {
  return request.put<{ user_id: number; role_ids: number[] }>(`/api/users/${id}/roles`, {
    role_ids: roleIds,
  })
}

/**
 * 修改用户状态
 * @param id 用户ID
 * @param status 状态：1正常 0禁用
 */
export const updateUserStatus = (id: number, status: number) => {
  return request.put<{ id: number; status: number }>(`/api/users/${id}/status`, { status })
}

/**
 * 重置用户密码
 * @param id 用户ID
 * @param password 新密码
 */
export const resetUserPassword = (id: number, password: string) => {
  return request.put<{ id: number; username: string }>(`/api/users/${id}/password`, { password })
}
