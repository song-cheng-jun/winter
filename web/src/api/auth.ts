/**
 * 认证相关API
 *
 * 包含用户登录、获取用户信息、登出等接口
 */

import { request } from '@/utils/request'
import type { LoginForm, LoginResponse, User } from '@/types'

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
 * 用户登出
 * @returns Promise 成功响应
 */
export const logout = () => {
  return request.post('/api/auth/logout')
}
