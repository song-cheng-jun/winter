/**
 * 用户状态管理
 *
 * 使用 Pinia Setup 语法（函数式写法）
 * 负责用户登录、登出、用户信息管理等
 *
 * 状态包括：
 * - token: JWT 认证令牌
 * - userInfo: 用户信息
 * - isLoggedIn: 是否已登录（计算属性）
 * - isAdmin: 是否是管理员（计算属性）
 */

import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { Storage, StorageKeys } from '@/utils/storage'
import { authApi } from '@/api'
import type { User, LoginForm } from '@/types'

export const useUserStore = defineStore('user', () => {
  // ============================
  // 状态
  // ============================

  // Token（从本地存储读取初始值）
  const token = ref<string>(Storage.getLocal<string>(StorageKeys.TOKEN) || '')

  // 用户信息（从本地存储读取初始值）
  const userInfo = ref<User | null>(Storage.getLocal<User>(StorageKeys.USER_INFO) || null)

  // ============================
  // 计算属性
  // ============================

  // 是否已登录
  const isLoggedIn = computed(() => !!token.value && !!userInfo.value)

  // 是否是管理员
  const isAdmin = computed(() => userInfo.value?.role === 'admin')

  // 用户昵称
  const nickname = computed(() => userInfo.value?.nickname || userInfo.value?.username || '')

  // ============================
  // 方法
  // ============================

  /**
   * 用户登录
   * @param loginForm 登录表单数据
   * @throws Error 登录失败时抛出错误
   */
  const loginAction = async (loginForm: LoginForm) => {
    try {
      // 调用登录接口
      const response = await authApi.login(loginForm)

      if (response.success && response.data) {
        const { token: newToken, userInfo: newUserInfo } = response.data

        // 保存Token和用户信息到状态
        token.value = newToken
        userInfo.value = newUserInfo

        // 持久化到本地存储
        Storage.setLocal(StorageKeys.TOKEN, newToken)
        Storage.setLocal(StorageKeys.USER_INFO, newUserInfo)

        return true
      }

      return false
    } catch (error) {
      console.error('登录失败:', error)
      throw error
    }
  }

  /**
   * 获取用户信息
   * @returns 用户信息或null
   * @throws Error 获取失败时抛出错误
   */
  const getUserInfoAction = async () => {
    try {
      const response = await authApi.getUserInfo()

      if (response.success && response.data) {
        // 更新用户信息
        userInfo.value = response.data

        // 持久化到本地存储
        Storage.setLocal(StorageKeys.USER_INFO, response.data)

        return response.data
      }

      return null
    } catch (error) {
      console.error('获取用户信息失败:', error)
      throw error
    }
  }

  /**
   * 用户登出
   */
  const logoutAction = async () => {
    try {
      // 调用登出接口（可选）
      await authApi.logout()
    } catch (error) {
      console.error('登出接口调用失败:', error)
    } finally {
      // 清空状态
      token.value = ''
      userInfo.value = null

      // 清空本地存储
      Storage.removeLocal(StorageKeys.TOKEN)
      Storage.removeLocal(StorageKeys.USER_INFO)
    }
  }

  /**
   * 更新Token
   * @param newToken 新Token
   */
  const updateToken = (newToken: string) => {
    token.value = newToken
    Storage.setLocal(StorageKeys.TOKEN, newToken)
  }

  /**
   * 清空用户信息
   */
  const clearUserInfo = () => {
    token.value = ''
    userInfo.value = null
    Storage.removeLocal(StorageKeys.TOKEN)
    Storage.removeLocal(StorageKeys.USER_INFO)
  }

  // ============================
  // 返回
  // ============================

  return {
    // 状态
    token,
    userInfo,

    // 计算属性
    isLoggedIn,
    isAdmin,
    nickname,

    // 方法
    loginAction,
    getUserInfoAction,
    logoutAction,
    updateToken,
    clearUserInfo,
  }
})
