<template>
  <div class="home-container">
    <header class="home-header">
      <div class="header-left">
        <h1>图书管理系统</h1>
      </div>

      <div class="header-right">
        <div class="user-info" @click="showUserMenu = !showUserMenu">
          <div class="avatar">{{ userStore.nickname?.charAt(0).toUpperCase() }}</div>
          <span class="username">{{ userStore.nickname }}</span>
          <span class="arrow">{{ showUserMenu ? '▲' : '▼' }}</span>
        </div>

        <div v-if="showUserMenu" class="user-menu">
          <div class="menu-item" @click="handleLogout">退出登录</div>
        </div>
      </div>
    </header>

    <main class="home-main">
      <div class="welcome-card">
        <h2>欢迎回来</h2>
        <div class="user-details">
          <div class="detail-item">
            <span class="label">用户名：</span>
            <span class="value">{{ userStore.userInfo?.username }}</span>
          </div>
          <div class="detail-item">
            <span class="label">昵称：</span>
            <span class="value">{{ userStore.userInfo?.nickname }}</span>
          </div>
          <div class="detail-item">
            <span class="label">角色：</span>
            <span class="value">{{ userStore.userInfo?.role === 'admin' ? '管理员' : '普通用户' }}</span>
          </div>
          <div v-if="userStore.userInfo?.last_login_time" class="detail-item">
            <span class="label">最后登录时间：</span>
            <span class="value">{{ userStore.userInfo.last_login_time }}</span>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'

const router = useRouter()
const userStore = useUserStore()

// 显示用户菜单
const showUserMenu = ref(false)

/**
 * 退出登录
 */
const handleLogout = async () => {
  await userStore.logoutAction()
  router.push('/login')
}

// 点击其他地方关闭菜单
const closeMenu = () => {
  showUserMenu.value = false
}

// 监听点击事件
document.addEventListener('click', (e) => {
  const target = e.target as HTMLElement
  if (!target.closest('.header-right')) {
    closeMenu()
  }
})
</script>

<style lang="scss" scoped>
.home-container {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.home-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 60px;
  background-color: #ffffff;
  border-bottom: 1px solid #ebeef5;
  padding: 0 20px;

  h1 {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
    color: #303133;
  }
}

.header-right {
  position: relative;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  cursor: pointer;
  border-radius: 4px;
  transition: background-color 0.2s;

  &:hover {
    background-color: #f5f7fa;
  }

  .avatar {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #409eff;
    color: #ffffff;
    border-radius: 50%;
    font-size: 14px;
    font-weight: 600;
  }

  .username {
    font-size: 14px;
    color: #606266;
  }

  .arrow {
    font-size: 10px;
    color: #909399;
  }
}

.user-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 5px;
  background-color: #ffffff;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  min-width: 120px;
  z-index: 10;

  .menu-item {
    padding: 10px 15px;
    font-size: 14px;
    color: #606266;
    cursor: pointer;
    transition: background-color 0.2s;

    &:hover {
      background-color: #f5f7fa;
    }

    &:first-child {
      border-radius: 4px 4px 0 0;
    }

    &:last-child {
      border-radius: 0 0 4px 4px;
    }
  }
}

.home-main {
  padding: 20px;
}

.welcome-card {
  background-color: #ffffff;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);

  h2 {
    font-size: 20px;
    font-weight: 600;
    color: #303133;
    margin: 0 0 20px 0;
  }

  .user-details {
    .detail-item {
      display: flex;
      padding: 10px 0;
      border-bottom: 1px solid #ebeef5;

      &:last-child {
        border-bottom: none;
      }

      .label {
        width: 120px;
        font-size: 14px;
        color: #909399;
      }

      .value {
        font-size: 14px;
        color: #303133;
      }
    }
  }
}
</style>
