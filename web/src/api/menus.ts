/**
 * 菜单管理 API
 *
 * 包含菜单 CRUD、树形结构查询等接口
 */

import { request } from '@/utils/request'
import type { ApiResponse, Menu, MenuForm, MenuListQuery } from '@/types'

/**
 * 获取菜单列表（树形结构）
 * @param params 查询参数
 */
export const getMenuList = (params: MenuListQuery = {}) => {
  return request.get<Menu[]>('/api/menus', { params })
}

/**
 * 获取菜单树（用于下拉选择）
 */
export const getMenuTree = () => {
  return request.get<Menu[]>('/api/menus/tree')
}

/**
 * 获取菜单详情
 * @param id 菜单ID
 */
export const getMenuDetail = (id: number) => {
  return request.get<Menu>(`/api/menus/${id}`)
}

/**
 * 创建菜单
 * @param data 菜单表单数据
 */
export const createMenu = (data: MenuForm) => {
  return request.post<{ id: number; name: string }>('/api/menus', data)
}

/**
 * 更新菜单信息
 * @param id 菜单ID
 * @param data 菜单表单数据
 */
export const updateMenu = (id: number, data: Partial<MenuForm>) => {
  return request.put<{ id: number; name: string }>(`/api/menus/${id}`, data)
}

/**
 * 删除菜单
 * @param id 菜单ID
 */
export const deleteMenu = (id: number) => {
  return request.delete(`/api/menus/${id}`)
}
