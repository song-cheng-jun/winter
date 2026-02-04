<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1 class="login-title">图书管理系统</h1>
        <p class="login-subtitle">用户登录</p>
      </div>

      <form class="login-form" @submit.prevent="handleLogin">
        <div class="form-item">
          <label for="username">用户名</label>
          <input
            id="username"
            v-model="loginForm.username"
            type="text"
            placeholder="请输入用户名"
            :disabled="loading"
            @blur="validateField('username')"
          />
          <span v-if="errors.username" class="error-text">{{ errors.username }}</span>
        </div>
        <div class="form-item">
          <label for="password">密码</label>
          <div class="password-input-wrapper">
            <input
              id="password"
              v-model="loginForm.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="请输入密码"
              :disabled="loading"
              @blur="validateField('password')"
            />
            <div class="eye-icon" @click="togglePasswordVisibility">
              <el-icon v-if="showPassword"><View/></el-icon>
              <el-icon v-else><Hide /></el-icon>
            </div>
          </div>
          <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
        </div>
        <button type="submit" class="login-button" :disabled="loading">
          {{ loading ? '登录中...' : '登录' }}
        </button>
      </form>

      <div class="login-footer">
        <p>测试账号: admin / admin123</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/user'
import type { LoginForm } from '@/types'

// 路由实例
const router = useRouter()

// 用户Store
const userStore = useUserStore()

// 加载状态
const loading = ref(false)

// 密码可见性
const showPassword = ref(false)

/**
 * 切换密码可见性
 */
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

// 表单数据
const loginForm = reactive<LoginForm>({
  username: '',
  password: '',
})

// 表单错误信息
const errors = reactive<Record<string, string>>({
  username: '',
  password: '',
})

/**
 * 验证单个字段
 * @param field 字段名
 */
const validateField = (field: keyof LoginForm) => {
  errors[field] = ''

  if (field === 'username') {
    if (!loginForm.username) {
      errors.username = '请输入用户名'
      return false
    }
    if (loginForm.username.length < 3) {
      errors.username = '用户名至少3个字符'
      return false
    }
    if (!/^[a-zA-Z0-9_]+$/.test(loginForm.username)) {
      errors.username = '用户名只能包含字母、数字和下划线'
      return false
    }
  }

  if (field === 'password') {
    if (!loginForm.password) {
      errors.password = '请输入密码'
      return false
    }
    if (loginForm.password.length < 6) {
      errors.password = '密码至少6个字符'
      return false
    }
  }

  return true
}

/**
 * 验证整个表单
 */
const validateForm = (): boolean => {
  let isValid = true

  if (!validateField('username')) isValid = false
  if (!validateField('password')) isValid = false

  return isValid
}

/**
 * 处理登录
 */
const handleLogin = async () => {
  // 表单验证
  if (!validateForm()) {
    return
  }

  // 开始登录
  loading.value = true

  try {
    // 调用登录接口
    const success = await userStore.loginAction(loginForm)

    if (success) {
      // 加载用户菜单
      await userStore.getUserMenusAction()
      // 加载用户权限
      await userStore.getUserPermissionsAction()

      // 登录成功，跳转到首页
      router.push('/')
    }
  } catch (error: any) {
    console.error('登录失败:', error)
    // 错误提示已在响应拦截器中处理
  } finally {
    loading.value = false
  }
}
</script>

<style lang="scss" scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  width: 420px;
  padding: 40px;
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.login-title {
  font-size: 28px;
  font-weight: 600;
  color: #303133;
  margin: 0 0 10px 0;
}

.login-subtitle {
  font-size: 14px;
  color: #909399;
  margin: 0;
}

.login-form {
  margin-top: 30px;
}

.form-item {
  margin-bottom: 20px;

  label {
    display: block;
    font-size: 14px;
    color: #606266;
    margin-bottom: 8px;
  }

  input {
    width: 100%;
    height: 40px;
    padding: 0 15px;
    border: 1px solid #dcdfe6;
    border-radius: 4px;
    font-size: 14px;
    color: #606266;
    transition: border-color 0.2s;

    &:focus {
      outline: none;
      border-color: #409eff;
    }

    &:disabled {
      background-color: #f5f7fa;
      cursor: not-allowed;
    }

    &::placeholder {
      color: #c0c4cc;
    }
  }

  .password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;

    input {
      padding-right: 40px;
    }

    .eye-icon {
      position: absolute;
      right: 12px;
      cursor: pointer;
      color: #909399;
      display: flex;
      align-items: center;
      justify-content: center;
      user-select: none;
      transition: color 0.2s;

      &:hover {
        color: #409eff;
      }
    }
  }

  .error-text {
    display: block;
    font-size: 12px;
    color: #f56c6c;
    margin-top: 5px;
  }
}

.login-button {
  width: 100%;
  height: 40px;
  margin-top: 10px;
  background-color: #409eff;
  border: none;
  border-radius: 4px;
  font-size: 14px;
  color: #ffffff;
  cursor: pointer;
  transition: background-color 0.2s;

  &:hover:not(:disabled) {
    background-color: #66b1ff;
  }

  &:active:not(:disabled) {
    background-color: #3a8ee6;
  }

  &:disabled {
    background-color: #a0cfff;
    cursor: not-allowed;
  }
}

.login-footer {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
  text-align: center;

  p {
    font-size: 12px;
    color: #909399;
    margin: 5px 0;
  }
}
</style>
