/**
 * 通用API响应格式
 */
export interface ApiResponse<T = any> {
  success: boolean
  message: string
  data?: T
  error?: string
  error_code?: number
}

/**
 * 用户信息
 */
export interface User {
  id: number
  username: string
  nickname: string
  avatar?: string
  email?: string
  phone?: string
  role: 'admin' | 'user'
  status: number
  last_login_time?: string
  last_login_ip?: string
  created_at: string
  updated_at: string
}

/**
 * 登录表单数据
 */
export interface LoginForm {
  username: string
  password: string
}

/**
 * 登录响应数据
 */
export interface LoginResponse {
  token: string
  userInfo: User
}

/**
 * 请求配置
 */
export interface RequestConfig {
  showErrorMessage?: boolean
  showSuccessMessage?: boolean
}

/**
 * 路由元信息
 */
export interface RouteMeta {
  title?: string
  requiresAuth?: boolean
  roles?: Array<'admin' | 'user'>
}

// 导出权限系统相关类型
export * from './permission'
