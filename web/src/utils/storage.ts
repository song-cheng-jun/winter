/**
 * 本地存储工具类
 * 用于管理 localStorage 和 sessionStorage
 *
 * 功能：
 * - 封装 localStorage 和 sessionStorage 的读写操作
 * - 自动处理 JSON 序列化和反序列化
 * - 提供类型安全的存储方法
 * - 统一的错误处理
 */
export class Storage {
  /**
   * 设置 localStorage
   * @param key 键名
   * @param value 值（任意类型，会被自动序列化为JSON）
   */
  static setLocal(key: string, value: any): void {
    try {
      localStorage.setItem(key, JSON.stringify(value))
    } catch (error) {
      console.error('localStorage set error:', error)
    }
  }

  /**
   * 获取 localStorage
   * @param key 键名
   * @param defaultValue 默认值（当key不存在或解析失败时返回）
   * @returns 存储的值或默认值
   */
  static getLocal<T = any>(key: string, defaultValue?: T): T | null {
    try {
      const item = localStorage.getItem(key)
      return item ? JSON.parse(item) : defaultValue ?? null
    } catch (error) {
      console.error('localStorage get error:', error)
      return defaultValue ?? null
    }
  }

  /**
   * 删除 localStorage
   * @param key 键名
   */
  static removeLocal(key: string): void {
    localStorage.removeItem(key)
  }

  /**
   * 清空 localStorage
   */
  static clearLocal(): void {
    localStorage.clear()
  }

  /**
   * 设置 sessionStorage
   * @param key 键名
   * @param value 值（任意类型，会被自动序列化为JSON）
   */
  static setSession(key: string, value: any): void {
    try {
      sessionStorage.setItem(key, JSON.stringify(value))
    } catch (error) {
      console.error('sessionStorage set error:', error)
    }
  }

  /**
   * 获取 sessionStorage
   * @param key 键名
   * @param defaultValue 默认值（当key不存在或解析失败时返回）
   * @returns 存储的值或默认值
   */
  static getSession<T = any>(key: string, defaultValue?: T): T | null {
    try {
      const item = sessionStorage.getItem(key)
      return item ? JSON.parse(item) : defaultValue ?? null
    } catch (error) {
      console.error('sessionStorage get error:', error)
      return defaultValue ?? null
    }
  }

  /**
   * 删除 sessionStorage
   * @param key 键名
   */
  static removeSession(key: string): void {
    sessionStorage.removeItem(key)
  }

  /**
   * 清空 sessionStorage
   */
  static clearSession(): void {
    sessionStorage.clear()
  }
}

/**
 * 存储键名常量
 * 统一管理所有存储键名，避免硬编码和拼写错误
 */
export const StorageKeys = {
  /** JWT Token */
  TOKEN: 'library_token',
  /** 用户信息 */
  USER_INFO: 'library_user_info',
  /** 用户角色列表 */
  USER_ROLES: 'library_user_roles',
  /** 用户菜单树 */
  USER_MENUS: 'library_user_menus',
  /** 用户权限代码列表 */
  USER_PERMISSIONS: 'library_user_permissions',
} as const
