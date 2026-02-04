/**
 * 认证相关API
 *
 * 包含用户登录、获取用户信息、登出、菜单、权限等接口
 */

import { request } from '@/utils/request'
import type { LoginForm, LoginResponse, User, Menu, UserCompleteInfo } from '@/types'

/**
 * 用户登录
 * @param data 登录表单数据（用户名和密码）
 * @returns Promise<LoginResponse> 包含Token和用户信息
 */
export const login = (data: LoginForm) => {
  return request.post<LoginResponse>('/api/auth/login', data)
}

/**
 * 获取当前登录用户信息
 * @returns Promise<User> 用户信息
 */
export const getUserInfo = () => {
  return request.get<User>('/api/auth/userinfo')
}

/**
 * 获取当前用户的菜单树
 * @returns Promise<Menu[]> 菜单树形结构
 */
export const getUserMenus = () => {
  return request.get<Menu[]>('/api/auth/menus')
}

/**
 * 获取当前用户的权限列表
 * @returns Promise<string[]> 权限代码数组
 */
export const getUserPermissions = () => {
  return request.get<string[]>('/api/auth/permissions')
}

/**
 * 获取当前用户完整信息（包含角色、菜单、权限）
 * @returns Promise<UserCompleteInfo> 用户完整信息
 */
export const getUserCompleteInfo = () => {
  return request.get<UserCompleteInfo>('/api/auth/info')
}

/**
 * 用户登出
 * @returns Promise 成功响应
 */
export const logout = () => {
  return request.post('/api/auth/logout')
}
