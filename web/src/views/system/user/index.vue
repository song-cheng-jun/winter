<template>
  <div class="user-management">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <el-form :inline="true" :model="queryForm" class="search-form">
        <el-form-item label="关键词">
          <el-input
            v-model="queryForm.keyword"
            placeholder="请输入用户名/昵称/邮箱/手机号"
            clearable
            @clear="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="queryForm.status" placeholder="请选择状态" clearable>
            <el-option label="全部" :value="undefined" />
            <el-option label="正常" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="角色">
          <el-select v-model="queryForm.role_id" placeholder="请选择角色" clearable>
            <el-option label="全部" :value="undefined" />
            <el-option
              v-for="role in roleList"
              :key="role.id"
              :label="role.name"
              :value="role.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 操作按钮 -->
      <div class="toolbar">
        <el-button type="primary" @click="handleCreate">
          <el-icon><Plus /></el-icon>
          新增用户
        </el-button>
      </div>

      <!-- 用户列表 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="nickname" label="昵称" width="120" />
        <el-table-column label="角色" width="200">
          <template #default="{ row }">
            <el-tag
              v-for="role in row.roles"
              :key="role.id"
              size="small"
              style="margin-right: 5px"
            >
              {{ role.name }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="email" label="邮箱" width="180" />
        <el-table-column prop="phone" label="手机号" width="130" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '正常' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="last_login_time" label="最后登录时间" width="180" />
        <el-table-column label="操作" width="300" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button link type="primary" size="small" @click="handleAssignRoles(row)">
              分配角色
            </el-button>
            <el-button link type="primary" size="small" @click="handleResetPassword(row)">
              重置密码
            </el-button>
            <el-button
              link
              :type="row.status === 1 ? 'warning' : 'success'"
              size="small"
              @click="handleToggleStatus(row)"
            >
              {{ row.status === 1 ? '禁用' : '启用' }}
            </el-button>
            <el-button link type="danger" size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="queryForm.page"
        v-model:page-size="queryForm.limit"
        :page-sizes="[10, 20, 50, 100]"
        :total="total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSearch"
        @current-change="handleSearch"
      />
    </el-card>

    <!-- 新增/编辑用户对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="500px"
      @close="handleDialogClose"
    >
      <el-form ref="formRef" :model="form" :rules="formRules" label-width="80px">
        <el-form-item label="用户名" prop="username">
          <el-input v-model="form.username" placeholder="请输入用户名" :disabled="isEdit" />
        </el-form-item>
        <el-form-item v-if="!isEdit" label="密码" prop="password">
          <el-input v-model="form.password" type="password" placeholder="请输入密码" />
        </el-form-item>
        <el-form-item label="昵称" prop="nickname">
          <el-input v-model="form.nickname" placeholder="请输入昵称" />
        </el-form-item>
        <el-form-item label="邮箱" prop="email">
          <el-input v-model="form.email" placeholder="请输入邮箱" />
        </el-form-item>
        <el-form-item label="手机号" prop="phone">
          <el-input v-model="form.phone" placeholder="请输入手机号" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 分配角色对话框 -->
    <el-dialog v-model="roleDialogVisible" title="分配角色" width="400px">
      <el-checkbox-group v-model="selectedRoleIds">
        <el-checkbox v-for="role in roleList" :key="role.id" :label="role.id">
          {{ role.name }}
        </el-checkbox>
      </el-checkbox-group>
      <template #footer>
        <el-button @click="roleDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="roleSubmitLoading" @click="handleRoleSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <!-- 重置密码对话框 -->
    <el-dialog v-model="passwordDialogVisible" title="重置密码" width="400px">
      <el-form ref="passwordFormRef" :model="passwordForm" :rules="passwordRules" label-width="80px">
        <el-form-item label="新密码" prop="password">
          <el-input v-model="passwordForm.password" type="password" placeholder="请输入新密码" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="passwordDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="passwordSubmitLoading" @click="handlePasswordSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { userApi, roleApi } from '@/api'
import type { UserInfo, UserForm, UserListQuery, Role } from '@/types'

// 查询表单
const queryForm = reactive<UserListQuery>({
  page: 1,
  limit: 20,
  keyword: '',
  status: undefined,
  role_id: undefined,
})

// 表格数据
const tableData = ref<UserInfo[]>([])
const total = ref(0)
const loading = ref(false)

// 对话框相关
const dialogVisible = ref(false)
const dialogTitle = computed(() => (isEdit.value ? '编辑用户' : '新增用户'))
const isEdit = ref(false)
const currentUserId = ref<number | null>(null)
const submitLoading = ref(false)

// 表单数据
const form = reactive<UserForm>({
  username: '',
  password: '',
  nickname: '',
  email: '',
  phone: '',
})
const formRef = ref<FormInstance>()
const formRules: FormRules = {
  username: [{ required: true, message: '请输入用户名', trigger: 'blur' }],
  password: [
    { required: true, message: '请输入密码', trigger: 'blur' },
    { min: 6, message: '密码至少6位', trigger: 'blur' },
  ],
}

// 角色对话框相关
const roleDialogVisible = ref(false)
const roleList = ref<Role[]>([])
const selectedRoleIds = ref<number[]>([])
const roleSubmitLoading = ref(false)
const roleUserId = ref<number | null>(null)

// 重置密码对话框相关
const passwordDialogVisible = ref(false)
const passwordForm = reactive({
  password: '',
})
const passwordFormRef = ref<FormInstance>()
const passwordRules: FormRules = {
  password: [
    { required: true, message: '请输入新密码', trigger: 'blur' },
    { min: 6, message: '密码至少6位', trigger: 'blur' },
  ],
}
const passwordSubmitLoading = ref(false)
const passwordUserId = ref<number | null>(null)

// 获取用户列表
const getUserList = async () => {
  loading.value = true
  try {
    const response = await userApi.getUserList(queryForm)
    if (response.success && response.data) {
      tableData.value = response.data.list
      total.value = response.data.total
    }
  } catch (error) {
    console.error('获取用户列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取角色列表
const getRoleList = async () => {
  try {
    const response = await roleApi.getRoleList({ page: 1, limit: 100 })
    if (response.success && response.data) {
      roleList.value = response.data.list
    }
  } catch (error) {
    console.error('获取角色列表失败:', error)
  }
}

// 搜索
const handleSearch = () => {
  queryForm.page = 1
  getUserList()
}

// 重置
const handleReset = () => {
  Object.assign(queryForm, {
    page: 1,
    limit: 20,
    keyword: '',
    status: undefined,
    role_id: undefined,
  })
  getUserList()
}

// 新增用户
const handleCreate = () => {
  isEdit.value = false
  currentUserId.value = null
  Object.assign(form, {
    username: '',
    password: '',
    nickname: '',
    email: '',
    phone: '',
  })
  dialogVisible.value = true
}

// 编辑用户
const handleEdit = (row: UserInfo) => {
  isEdit.value = true
  currentUserId.value = row.id
  Object.assign(form, {
    username: row.username,
    nickname: row.nickname,
    email: row.email,
    phone: row.phone,
  })
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    submitLoading.value = true
    try {
      if (isEdit.value && currentUserId.value) {
        await userApi.updateUser(currentUserId.value, form)
        ElMessage.success('更新用户成功')
      } else {
        await userApi.createUser(form)
        ElMessage.success('创建用户成功')
      }
      dialogVisible.value = false
      getUserList()
    } catch (error) {
      console.error('提交失败:', error)
    } finally {
      submitLoading.value = false
    }
  })
}

// 对话框关闭
const handleDialogClose = () => {
  formRef.value?.resetFields()
}

// 分配角色
const handleAssignRoles = async (row: UserInfo) => {
  roleUserId.value = row.id
  selectedRoleIds.value = row.roles?.map((r) => r.id) || []
  roleDialogVisible.value = true
}

// 提交角色分配
const handleRoleSubmit = async () => {
  if (!roleUserId.value) return
  roleSubmitLoading.value = true
  try {
    await userApi.assignUserRoles(roleUserId.value, selectedRoleIds.value)
    ElMessage.success('角色分配成功')
    roleDialogVisible.value = false
    getUserList()
  } catch (error) {
    console.error('角色分配失败:', error)
  } finally {
    roleSubmitLoading.value = false
  }
}

// 重置密码
const handleResetPassword = (row: UserInfo) => {
  passwordUserId.value = row.id
  passwordForm.password = ''
  passwordDialogVisible.value = true
}

// 提交重置密码
const handlePasswordSubmit = async () => {
  if (!passwordFormRef.value || !passwordUserId.value) return
  await passwordFormRef.value.validate(async (valid) => {
    if (!valid) return
    passwordSubmitLoading.value = true
    try {
      await userApi.resetUserPassword(passwordUserId.value, passwordForm.password)
      ElMessage.success('重置密码成功')
      passwordDialogVisible.value = false
    } catch (error) {
      console.error('重置密码失败:', error)
    } finally {
      passwordSubmitLoading.value = false
    }
  })
}

// 切换状态
const handleToggleStatus = async (row: UserInfo) => {
  const action = row.status === 1 ? '禁用' : '启用'
  try {
    await ElMessageBox.confirm(`确定要${action}用户"${row.username}"吗？`, '提示', {
      type: 'warning',
    })
    await userApi.updateUserStatus(row.id, row.status === 1 ? 0 : 1)
    ElMessage.success(`${action}成功`)
    getUserList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('切换状态失败:', error)
    }
  }
}

// 删除用户
const handleDelete = async (row: UserInfo) => {
  try {
    await ElMessageBox.confirm(`确定要删除用户"${row.username}"吗？`, '提示', {
      type: 'warning',
    })
    await userApi.deleteUser(row.id)
    ElMessage.success('删除成功')
    getUserList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
    }
  }
}

onMounted(() => {
  getUserList()
  getRoleList()
})
</script>

<style scoped lang="scss">
.user-management {
  .search-form {
    margin-bottom: 20px;
  }

  .toolbar {
    margin-bottom: 20px;
  }

  .el-pagination {
    margin-top: 20px;
    justify-content: flex-end;
  }
}
</style>
