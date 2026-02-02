/**
 * Axios 请求封装
 *
 * 功能：
 * - 统一的请求配置
 * - 请求拦截器：自动添加 Token 到请求头
 * - 响应拦截器：统一处理错误和业务状态码
 * - 401 自动跳转登录页
 * - 统一的错误提示
 */

import axios, { type AxiosInstance, type AxiosError, type InternalAxiosRequestConfig, type AxiosResponse } from 'axios'
import { Storage, StorageKeys } from './storage'
import type { ApiResponse, RequestConfig } from '@/types'

/**
 * Axios实例配置
 */
const config = {
  // API基础URL，从环境变量读取，默认为本地开发地址
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://lib.com',
  // 请求超时时间（毫秒）
  timeout: 15000,
}

/**
 * 创建Axios实例
 */
const service: AxiosInstance = axios.create(config)

/**
 * 请求拦截器
 * 在请求发送前，自动添加Token到请求头
 */
service.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // 从本地存储获取Token
    const token = Storage.getLocal<string>(StorageKeys.TOKEN)

    // 如果存在Token，添加到请求头
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    return config
  },
  (error: AxiosError) => {
    console.error('请求错误:', error)
    return Promise.reject(error)
  }
)

/**
 * 响应拦截器
 * 统一处理响应数据和错误
 */
service.interceptors.response.use(
  (response: AxiosResponse<ApiResponse>) => {
    const { data } = response

    // 检查业务状态码
    if (data.success === false) {
      // 业务错误，显示错误提示
      // 这里使用原生 alert，实际项目中可以使用 Element Plus 的 ElMessage
      console.error(data.message || '请求失败')
      return Promise.reject(new Error(data.message || '请求失败'))
    }

    // 成功响应，返回数据
    return response.data
  },
  (error: AxiosError<ApiResponse>) => {
    // 网络错误或服务器错误
    if (error.response) {
      const { status, data } = error.response

      // 处理不同的HTTP状态码
      switch (status) {
        case 401:
          // 未授权，清除本地存储，跳转到登录页
          console.error(data?.message || '登录已过期，请重新登录')
          Storage.removeLocal(StorageKeys.TOKEN)
          Storage.removeLocal(StorageKeys.USER_INFO)
          window.location.href = '/login'
          break
        case 403:
          console.error(data?.message || '没有权限访问')
          break
        case 404:
          console.error(data?.message || '请求的资源不存在')
          break
        case 500:
          console.error(data?.message || '服务器错误')
          break
        default:
          console.error(data?.message || `请求失败 (${status})`)
      }
    } else if (error.request) {
      // 请求已发送但没有收到响应
      console.error('网络错误，请检查网络连接')
    } else {
      // 请求配置出错
      console.error('请求配置错误')
    }

    return Promise.reject(error)
  }
)

/**
 * 封装请求方法
 */
export const request = {
  /**
   * GET请求
   * @param url 请求地址
   * @param params 请求参数
   * @param config 额外配置
   */
  get<T = any>(url: string, params?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return service.get(url, { params, ...config })
  },

  /**
   * POST请求
   * @param url 请求地址
   * @param data 请求体
   * @param config 额外配置
   */
  post<T = any>(url: string, data?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return service.post(url, data, config)
  },

  /**
   * PUT请求
   * @param url 请求地址
   * @param data 请求体
   * @param config 额外配置
   */
  put<T = any>(url: string, data?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return service.put(url, data, config)
  },

  /**
   * DELETE请求
   * @param url 请求地址
   * @param params 请求参数
   * @param config 额外配置
   */
  delete<T = any>(url: string, params?: any, config?: RequestConfig): Promise<ApiResponse<T>> {
    return service.delete(url, { params, ...config })
  },
}

export default service
