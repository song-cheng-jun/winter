<template>
  <div class="role-management">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <el-form :inline="true" :model="queryForm" class="search-form">
        <el-form-item label="关键词">
          <el-input
            v-model="queryForm.keyword"
            placeholder="请输入角色名称/代码"
            clearable
            @clear="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="queryForm.status" placeholder="请选择状态" clearable>
            <el-option label="全部" :value="undefined" />
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
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
          新增角色
        </el-button>
      </div>

      <!-- 角色列表 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="角色名称" width="150" />
        <el-table-column prop="code" label="角色代码" width="150" />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="180" />
        <el-table-column label="操作" width="320" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button link type="primary" size="small" @click="handleAssignPermissions(row)">
              分配权限
            </el-button>
            <el-button link type="primary" size="small" @click="handleAssignMenus(row)">
              分配菜单
            </el-button>
            <el-button link type="primary" size="small" @click="handleViewUsers(row)">
              查看用户
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

    <!-- 新增/编辑角色对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="500px"
      @close="handleDialogClose"
    >
      <el-form ref="formRef" :model="form" :rules="formRules" label-width="80px">
        <el-form-item label="角色名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入角色名称" />
        </el-form-item>
        <el-form-item label="角色代码" prop="code">
          <el-input v-model="form.code" placeholder="请输入角色代码" :disabled="isEdit" />
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="form.description" type="textarea" placeholder="请输入角色描述" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="form.sort" :min="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="submitLoading" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 分配权限对话框 -->
    <el-dialog v-model="permissionDialogVisible" title="分配权限" width="600px">
      <div v-loading="permissionLoading" class="permission-tree">
        <el-tree
          ref="permissionTreeRef"
          :data="permissionTreeData"
          :props="{ children: 'children', label: 'name' }"
          node-key="id"
          show-checkbox
          default-expand-all
        >
          <template #default="{ node, data }">
            <span class="custom-tree-node">
              <span>{{ data.name }}</span>
              <el-tag v-if="data.code" size="small" type="info">{{ data.code }}</el-tag>
            </span>
          </template>
        </el-tree>
      </div>
      <template #footer>
        <el-button @click="permissionDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="permissionSubmitLoading" @click="handlePermissionSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <!-- 分配菜单对话框 -->
    <el-dialog v-model="menuDialogVisible" title="分配菜单" width="500px">
      <div v-loading="menuLoading" class="menu-tree">
        <el-tree
          ref="menuTreeRef"
          :data="menuTreeData"
          :props="{ children: 'children', label: 'title' }"
          node-key="id"
          show-checkbox
          default-expand-all
        />
      </div>
      <template #footer>
        <el-button @click="menuDialogVisible = false">取消</el-button>
        <el-button type="primary" :loading="menuSubmitLoading" @click="handleMenuSubmit">
          确定
        </el-button>
      </template>
    </el-dialog>

    <!-- 查看用户对话框 -->
    <el-dialog v-model="userDialogVisible" title="角色用户" width="800px">
      <el-table v-loading="userLoading" :data="roleUsers" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户名" width="120" />
        <el-table-column prop="nickname" label="昵称" width="120" />
        <el-table-column prop="email" label="邮箱" min-width="180" />
        <el-table-column prop="phone" label="手机号" width="130" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '正常' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { roleApi, permissionApi, menuApi } from '@/api'
import type { Role, RoleForm, ListQuery, Permission, Menu, UserInfo } from '@/types'

// 查询表单
const queryForm = reactive<ListQuery>({
  page: 1,
  limit: 20,
  keyword: '',
  status: undefined,
})

// 表格数据
const tableData = ref<Role[]>([])
const total = ref(0)
const loading = ref(false)

// 对话框相关
const dialogVisible = ref(false)
const dialogTitle = computed(() => (isEdit.value ? '编辑角色' : '新增角色'))
const isEdit = ref(false)
const currentRoleId = ref<number | null>(null)
const submitLoading = ref(false)

// 表单数据
const form = reactive<RoleForm>({
  name: '',
  code: '',
  description: '',
  sort: 1,
})
const formRef = ref<FormInstance>()
const formRules: FormRules = {
  name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }],
  code: [{ required: true, message: '请输入角色代码', trigger: 'blur' }],
}

// 权限对话框相关
const permissionDialogVisible = ref(false)
const permissionTreeRef = ref()
const permissionTreeData = ref<any[]>([])
const permissionLoading = ref(false)
const permissionSubmitLoading = ref(false)
const permissionRoleId = ref<number | null>(null)

// 菜单对话框相关
const menuDialogVisible = ref(false)
const menuTreeRef = ref()
const menuTreeData = ref<Menu[]>([])
const menuLoading = ref(false)
const menuSubmitLoading = ref(false)
const menuRoleId = ref<number | null>(null)

// 用户对话框相关
const userDialogVisible = ref(false)
const roleUsers = ref<UserInfo[]>([])
const userLoading = ref(false)

// 获取角色列表
const getRoleList = async () => {
  loading.value = true
  try {
    const response = await roleApi.getRoleList(queryForm)
    if (response.success && response.data) {
      tableData.value = response.data.list
      total.value = response.data.total
    }
  } catch (error) {
    console.error('获取角色列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  queryForm.page = 1
  getRoleList()
}

// 重置
const handleReset = () => {
  Object.assign(queryForm, {
    page: 1,
    limit: 20,
    keyword: '',
    status: undefined,
  })
  getRoleList()
}

// 新增角色
const handleCreate = () => {
  isEdit.value = false
  currentRoleId.value = null
  Object.assign(form, {
    name: '',
    code: '',
    description: '',
    sort: 1,
  })
  dialogVisible.value = true
}

// 编辑角色
const handleEdit = (row: Role) => {
  isEdit.value = true
  currentRoleId.value = row.id
  Object.assign(form, {
    name: row.name,
    code: row.code,
    description: row.description,
    sort: row.sort,
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
      if (isEdit.value && currentRoleId.value) {
        await roleApi.updateRole(currentRoleId.value, form)
        ElMessage.success('更新角色成功')
      } else {
        await roleApi.createRole(form)
        ElMessage.success('创建角色成功')
      }
      dialogVisible.value = false
      getRoleList()
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

// 分配权限
const handleAssignPermissions = async (row: Role) => {
  permissionRoleId.value = row.id
  permissionDialogVisible.value = true
  permissionLoading.value = true

  try {
    // 获取权限分组数据
    const groupResponse = await permissionApi.getPermissionGroup()
    if (groupResponse.success && groupResponse.data) {
      // 转换为树形结构
      permissionTreeData.value = Object.entries(groupResponse.data).map(
        ([groupName, permissions]) => ({
          id: `group_${groupName}`,
          name: groupName,
          children: permissions.map((p: Permission) => ({
            id: p.id,
            name: p.name,
            code: p.code,
          })),
        })
      )
    }

    // 获取角色已有权限
    const rolePermResponse = await roleApi.getRolePermissions(row.id)
    if (rolePermResponse.success && rolePermResponse.data) {
      const checkedKeys = rolePermResponse.data.map((p) => p.id)
      setTimeout(() => {
        permissionTreeRef.value?.setCheckedKeys(checkedKeys)
      }, 100)
    }
  } catch (error) {
    console.error('获取权限数据失败:', error)
  } finally {
    permissionLoading.value = false
  }
}

// 提交权限分配
const handlePermissionSubmit = async () => {
  if (!permissionRoleId.value) return
  permissionSubmitLoading.value = true

  try {
    // 获取选中的权限ID（排除分组节点）
    const checkedNodes = permissionTreeRef.value?.getCheckedNodes() || []
    const permissionIds = checkedNodes
      .filter((node: any) => !node.id.startsWith('group_'))
      .map((node: any) => node.id)

    await roleApi.assignRolePermissions(permissionRoleId.value, permissionIds)
    ElMessage.success('权限分配成功')
    permissionDialogVisible.value = false
  } catch (error) {
    console.error('权限分配失败:', error)
  } finally {
    permissionSubmitLoading.value = false
  }
}

// 分配菜单
const handleAssignMenus = async (row: Role) => {
  menuRoleId.value = row.id
  menuDialogVisible.value = true
  menuLoading.value = true

  try {
    // 获取菜单树
    const menuResponse = await menuApi.getMenuList({ status: 1 })
    if (menuResponse.success && menuResponse.data) {
      menuTreeData.value = buildMenuTree(menuResponse.data, 0)
    }

    // 获取角色已有菜单
    const roleMenuResponse = await roleApi.getRoleMenus(row.id)
    if (roleMenuResponse.success && roleMenuResponse.data) {
      const checkedKeys = roleMenuResponse.data.map((m) => m.id)
      setTimeout(() => {
        menuTreeRef.value?.setCheckedKeys(checkedKeys)
      }, 100)
    }
  } catch (error) {
    console.error('获取菜单数据失败:', error)
  } finally {
    menuLoading.value = false
  }
}

// 构建菜单树
const buildMenuTree = (menus: Menu[], parentId: number): Menu[] => {
  return menus
    .filter((m) => m.parent_id === parentId)
    .map((m) => ({
      ...m,
      children: buildMenuTree(menus, m.id),
    }))
}

// 提交菜单分配
const handleMenuSubmit = async () => {
  if (!menuRoleId.value) return
  menuSubmitLoading.value = true

  try {
    const checkedKeys = menuTreeRef.value?.getCheckedKeys() || []
    await roleApi.assignRoleMenus(menuRoleId.value, checkedKeys)
    ElMessage.success('菜单分配成功')
    menuDialogVisible.value = false
  } catch (error) {
    console.error('菜单分配失败:', error)
  } finally {
    menuSubmitLoading.value = false
  }
}

// 查看用户
const handleViewUsers = async (row: Role) => {
  userDialogVisible.value = true
  userLoading.value = true

  try {
    const response = await roleApi.getRoleUsers(row.id)
    if (response.success && response.data) {
      roleUsers.value = response.data
    }
  } catch (error) {
    console.error('获取角色用户失败:', error)
  } finally {
    userLoading.value = false
  }
}

// 删除角色
const handleDelete = async (row: Role) => {
  try {
    await ElMessageBox.confirm(`确定要删除角色"${row.name}"吗？`, '提示', {
      type: 'warning',
    })
    await roleApi.deleteRole(row.id)
    ElMessage.success('删除成功')
    getRoleList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
    }
  }
}

onMounted(() => {
  getRoleList()
})
</script>

<style scoped lang="scss">
.role-management {
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

  .permission-tree,
  .menu-tree {
    max-height: 400px;
    overflow-y: auto;
  }

  .custom-tree-node {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
  }
}
</style>
