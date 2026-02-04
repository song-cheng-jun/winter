/**
 * 路由配置
 *
 * 包含：
 * - 路由定义
 * - 全局前置守卫（登录状态验证）
 */

import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import { useUserStore } from '@/stores/user'

/**
 * 路由配置
 */
const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/Login.vue'),
    meta: {
      title: '登录',
      requiresAuth: false,
    },
  },
  {
    path: '/',
    component: () => import('@/layouts/AdminLayout.vue'),
    redirect: '/home',
    meta: {
      requiresAuth: true,
    },
    children: [
      {
        path: '/home',
        name: 'Home',
        component: () => import('@/views/Home.vue'),
        meta: {
          title: '首页',
          requiresAuth: true,
        },
      },
      // 系统管理路由
      {
        path: '/system/user',
        name: 'SystemUser',
        component: () => import('@/views/system/user/index.vue'),
        meta: {
          title: '用户管理',
          requiresAuth: true,
        },
      },
      {
        path: '/system/role',
        name: 'SystemRole',
        component: () => import('@/views/system/role/index.vue'),
        meta: {
          title: '角色管理',
          requiresAuth: true,
        },
      },
      {
        path: '/system/menu',
        name: 'SystemMenu',
        component: () => import('@/views/system/menu/index.vue'),
        meta: {
          title: '菜单管理',
          requiresAuth: true,
        },
      },
      {
        path: '/system/permission',
        name: 'SystemPermission',
        component: () => import('@/views/system/permission/index.vue'),
        meta: {
          title: '权限管理',
          requiresAuth: true,
        },
      },
    ],
  },
  {
    // 404 重定向
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    redirect: '/',
  },
]

/**
 * 创建路由实例
 */
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

/**
 * 全局前置守卫
 * 用于验证用户登录状态
 *
 * 功能：
 * 1. 设置页面标题
 * 2. 检查路由是否需要登录
 * 3. 未登录用户访问受保护路由时跳转到登录页
 * 4. 已登录用户访问登录页时跳转到首页
 */
router.beforeEach((to, from, next) => {
  // 设置页面标题
  if (to.meta.title) {
    document.title = `${to.meta.title} - 图书管理系统`
  }

  // 获取用户Store（需要在使用时获取，避免在模块加载时就获取）
  const userStore = useUserStore()

  // 判断是否需要登录
  const requiresAuth = to.meta.requiresAuth !== false

  if (requiresAuth && !userStore.isLoggedIn) {
    // 需要登录但未登录，跳转到登录页
    next({
      path: '/login',
      query: { redirect: to.fullPath }, // 保存原始路径，登录后可以跳转回去
    })
  } else if (to.path === '/login' && userStore.isLoggedIn) {
    // 已登录用户访问登录页，跳转到首页
    next('/')
  } else {
    // 正常访问
    next()
  }
})

export default router
