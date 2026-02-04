/**
 * 用户状态管理
 *
 * 使用 Pinia Setup 语法（函数式写法）
 * 负责用户登录、登出、用户信息管理等
 *
 * 状态包括：
 * - token: JWT 认证令牌
 * - userInfo: 用户信息
 * - roles: 用户角色列表
 * - menus: 用户菜单树
 * - permissions: 用户权限代码列表
 * - isLoggedIn: 是否已登录（计算属性）
 * - isAdmin: 是否是管理员（计算属性）
 */

import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { Storage, StorageKeys } from '@/utils/storage'
import { authApi } from '@/api'
import type { User, LoginForm, Role, Menu } from '@/types'

export const useUserStore = defineStore('user', () => {
  // ============================
  // 状态
  // ============================

  // Token（从本地存储读取初始值）
  const token = ref<string>(Storage.getLocal<string>(StorageKeys.TOKEN) || '')

  // 用户信息（从本地存储读取初始值）
  const userInfo = ref<User | null>(Storage.getLocal<User>(StorageKeys.USER_INFO) || null)

  // 用户角色列表
  const roles = ref<Role[]>(Storage.getLocal<Role[]>(StorageKeys.USER_ROLES) || [])

  // 用户菜单树
  const menus = ref<Menu[]>(Storage.getLocal<Menu[]>(StorageKeys.USER_MENUS) || [])

  // 用户权限代码列表
  const permissions = ref<string[]>(Storage.getLocal<string[]>(StorageKeys.USER_PERMISSIONS) || [])

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
      roles.value = []
      menus.value = []
      permissions.value = []

      // 清空本地存储
      Storage.removeLocal(StorageKeys.TOKEN)
      Storage.removeLocal(StorageKeys.USER_INFO)
      Storage.removeLocal(StorageKeys.USER_ROLES)
      Storage.removeLocal(StorageKeys.USER_MENUS)
      Storage.removeLocal(StorageKeys.USER_PERMISSIONS)
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
    roles.value = []
    menus.value = []
    permissions.value = []
    Storage.removeLocal(StorageKeys.TOKEN)
    Storage.removeLocal(StorageKeys.USER_INFO)
    Storage.removeLocal(StorageKeys.USER_ROLES)
    Storage.removeLocal(StorageKeys.USER_MENUS)
    Storage.removeLocal(StorageKeys.USER_PERMISSIONS)
  }

  /**
   * 获取用户完整信息（角色、菜单、权限）
   */
  const getUserCompleteInfoAction = async () => {
    try {
      const response = await authApi.getUserCompleteInfo()

      if (response.success && response.data) {
        const { user, roles: userRoles, menus: userMenus, permissions: userPermissions } =
          response.data

        // 更新用户信息
        userInfo.value = { ...user, role: 'admin' } // 临时兼容旧的 role 字段
        roles.value = userRoles
        menus.value = userMenus
        permissions.value = userPermissions

        // 持久化到本地存储
        Storage.setLocal(StorageKeys.USER_INFO, userInfo.value)
        Storage.setLocal(StorageKeys.USER_ROLES, userRoles)
        Storage.setLocal(StorageKeys.USER_MENUS, userMenus)
        Storage.setLocal(StorageKeys.USER_PERMISSIONS, userPermissions)

        return response.data
      }

      return null
    } catch (error) {
      console.error('获取用户完整信息失败:', error)
      throw error
    }
  }

  /**
   * 获取用户菜单
   */
  const getUserMenusAction = async () => {
    try {
      const response = await authApi.getUserMenus()

      if (response.success && response.data) {
        menus.value = response.data
        Storage.setLocal(StorageKeys.USER_MENUS, response.data)
        return response.data
      }

      return null
    } catch (error) {
      console.error('获取用户菜单失败:', error)
      throw error
    }
  }

  /**
   * 获取用户权限
   */
  const getUserPermissionsAction = async () => {
    try {
      const response = await authApi.getUserPermissions()

      if (response.success && response.data) {
        permissions.value = response.data
        Storage.setLocal(StorageKeys.USER_PERMISSIONS, response.data)
        return response.data
      }

      return null
    } catch (error) {
      console.error('获取用户权限失败:', error)
      throw error
    }
  }

  /**
   * 检查用户是否有指定权限
   * @param code 权限代码
   */
  const hasPermission = (code: string) => {
    return permissions.value.includes(code)
  }

  // ============================
  // 返回
  // ============================

  return {
    // 状态
    token,
    userInfo,
    roles,
    menus,
    permissions,

    // 计算属性
    isLoggedIn,
    isAdmin,
    nickname,

    // 方法
    loginAction,
    getUserInfoAction,
    getUserCompleteInfoAction,
    getUserMenusAction,
    getUserPermissionsAction,
    logoutAction,
    updateToken,
    clearUserInfo,
    hasPermission,
  }
})
