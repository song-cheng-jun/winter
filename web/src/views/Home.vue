<template>
  <div class="dashboard">
    <el-row :gutter="20">
      <el-col :span="6" v-for="stat in statistics" :key="stat.title">
        <el-card shadow="hover" class="stat-card">
          <div class="stat-content">
            <div class="stat-icon" :style="{ backgroundColor: stat.color }">
              <el-icon :size="32">
                <component :is="stat.icon" />
              </el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ stat.value }}</div>
              <div class="stat-title">{{ stat.title }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="12">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>快捷操作</span>
            </div>
          </template>
          <div class="quick-actions">
            <el-button type="primary" @click="$router.push('/system/user')">
              <el-icon><User /></el-icon>
              用户管理
            </el-button>
            <el-button type="success" @click="$router.push('/system/role')">
              <el-icon><UserFilled /></el-icon>
              角色管理
            </el-button>
            <el-button type="warning" @click="$router.push('/system/menu')">
              <el-icon><Menu /></el-icon>
              菜单管理
            </el-button>
            <el-button type="info" @click="$router.push('/system/permission')">
              <el-icon><Lock /></el-icon>
              权限管理
            </el-button>
          </div>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>系统信息</span>
            </div>
          </template>
          <div class="system-info">
            <div class="info-item">
              <span class="label">系统名称：</span>
              <span class="value">图书管理系统</span>
            </div>
            <div class="info-item">
              <span class="label">版本号：</span>
              <span class="value">v1.0.0</span>
            </div>
            <div class="info-item">
              <span class="label">登录用户：</span>
              <span class="value">{{ userStore.nickname }}</span>
            </div>
            <div class="info-item">
              <span class="label">用户角色：</span>
              <span class="value">
                <el-tag
                  v-for="role in userStore.roles"
                  :key="role.id"
                  size="small"
                  style="margin-right: 5px"
                >
                  {{ role.name }}
                </el-tag>
              </span>
            </div>
            <div class="info-item">
              <span class="label">最后登录：</span>
              <span class="value">{{ userStore.userInfo?.last_login_time || '-' }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="24">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>欢迎使用图书管理系统</span>
            </div>
          </template>
          <div class="welcome-content">
            <p>这是一个基于 RBAC 权限管理的图书管理系统，支持：</p>
            <ul>
              <li>用户管理 - 管理系统用户，分配角色</li>
              <li>角色管理 - 创建角色，分配权限和菜单</li>
              <li>菜单管理 - 管理系统菜单，支持无限级树形结构</li>
              <li>权限管理 - 管理系统权限，精细化控制</li>
            </ul>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useUserStore } from '@/stores/user'
import { User, UserFilled, Menu, Lock, Document, Reading, Setting } from '@element-plus/icons-vue'

const userStore = useUserStore()

// 统计数据
const statistics = ref([
  { title: '用户总数', value: '0', icon: User, color: '#409eff' },
  { title: '角色总数', value: '0', icon: UserFilled, color: '#67c23a' },
  { title: '菜单总数', value: '0', icon: Menu, color: '#e6a23c' },
  { title: '权限总数', value: '0', icon: Lock, color: '#f56c6c' },
])
</script>

<style scoped lang="scss">
.dashboard {
  .stat-card {
    .stat-content {
      display: flex;
      align-items: center;
      gap: 20px;

      .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
      }

      .stat-info {
        flex: 1;

        .stat-value {
          font-size: 28px;
          font-weight: bold;
          color: #303133;
          line-height: 1;
          margin-bottom: 8px;
        }

        .stat-title {
          font-size: 14px;
          color: #909399;
        }
      }
    }
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
  }

  .quick-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;

    .el-button {
      flex: 1;
      min-width: 120px;
    }
  }

  .system-info {
    .info-item {
      display: flex;
      padding: 12px 0;
      border-bottom: 1px solid #f0f0f0;

      &:last-child {
        border-bottom: none;
      }

      .label {
        width: 100px;
        color: #909399;
        font-size: 14px;
      }

      .value {
        flex: 1;
        color: #303133;
        font-size: 14px;
      }
    }
  }

  .welcome-content {
    line-height: 1.8;

    p {
      margin: 0 0 10px 0;
      color: #606266;
    }

    ul {
      margin: 0;
      padding-left: 20px;
      color: #606266;

      li {
        margin: 5px 0;
      }
    }
  }
}
</style>
