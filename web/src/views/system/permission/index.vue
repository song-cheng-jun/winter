<template>
  <div class="permission-management">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <el-form :inline="true" :model="queryForm" class="search-form">
        <el-form-item label="关键词">
          <el-input
            v-model="queryForm.keyword"
            placeholder="请输入权限名称/代码"
            clearable
            @clear="handleSearch"
          />
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="queryForm.type" placeholder="请选择类型" clearable>
            <el-option label="全部" :value="undefined" />
            <el-option label="API" value="api" />
            <el-option label="菜单" value="menu" />
            <el-option label="按钮" value="button" />
          </el-select>
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
          新增权限
        </el-button>
      </div>

      <!-- 权限列表 -->
      <el-table v-loading="loading" :data="tableData" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="权限名称" width="150" />
        <el-table-column prop="code" label="权限代码" width="180" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getTypeTagType(row.type)">
              {{ getTypeName(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
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
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEdit(row)">
              编辑
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

    <!-- 新增/编辑权限对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="500px"
      @close="handleDialogClose"
    >
      <el-form ref="formRef" :model="form" :rules="formRules" label-width="80px">
        <el-form-item label="关联菜单" prop="menu_id">
          <el-tree-select
            v-model="form.menu_id"
            :data="menuTreeOptions"
            :props="{ value: 'id', label: 'title', children: 'children' }"
            placeholder="请选择关联菜单"
            check-strictly
            clearable
          />
        </el-form-item>
        <el-form-item label="权限名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入权限名称" />
        </el-form-item>
        <el-form-item label="权限代码" prop="code">
          <el-input v-model="form.code" placeholder="请输入权限代码，如：user:create" />
        </el-form-item>
        <el-form-item label="权限类型" prop="type">
          <el-radio-group v-model="form.type">
            <el-radio value="api">API</el-radio>
            <el-radio value="menu">菜单</el-radio>
            <el-radio value="button">按钮</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="form.description" type="textarea" placeholder="请输入权限描述" />
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
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox, type FormInstance, type FormRules } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { permissionApi, menuApi } from '@/api'
import type { Permission, PermissionForm, PermissionListQuery, Menu } from '@/types'

// 查询表单
const queryForm = reactive<PermissionListQuery>({
  page: 1,
  limit: 20,
  keyword: '',
  type: undefined,
  status: undefined,
})

// 表格数据
const tableData = ref<Permission[]>([])
const total = ref(0)
const loading = ref(false)

// 菜单树选项
const menuTreeOptions = ref<Menu[]>([])

// 对话框相关
const dialogVisible = ref(false)
const dialogTitle = computed(() => (isEdit.value ? '编辑权限' : '新增权限'))
const isEdit = ref(false)
const currentPermissionId = ref<number | null>(null)
const submitLoading = ref(false)

// 表单数据
const form = reactive<PermissionForm>({
  menu_id: undefined,
  name: '',
  code: '',
  type: 'api',
  description: '',
  sort: 1,
})
const formRef = ref<FormInstance>()
const formRules: FormRules = {
  name: [{ required: true, message: '请输入权限名称', trigger: 'blur' }],
  code: [{ required: true, message: '请输入权限代码', trigger: 'blur' }],
  type: [{ required: true, message: '请选择权限类型', trigger: 'change' }],
}

// 获取类型名称
const getTypeName = (type: string) => {
  const map: Record<string, string> = {
    api: 'API',
    menu: '菜单',
    button: '按钮',
  }
  return map[type] || type
}

// 获取类型标签类型
const getTypeTagType = (type: string) => {
  const map: Record<string, any> = {
    api: 'primary',
    menu: 'success',
    button: 'warning',
  }
  return map[type] || ''
}

// 获取权限列表
const getPermissionList = async () => {
  loading.value = true
  try {
    const response = await permissionApi.getPermissionList(queryForm)
    if (response.success && response.data) {
      tableData.value = response.data.list
      total.value = response.data.total
    }
  } catch (error) {
    console.error('获取权限列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取菜单树选项
const getMenuTreeOptions = async () => {
  try {
    const response = await menuApi.getMenuTree()
    if (response.success && response.data) {
      menuTreeOptions.value = buildMenuTree(response.data, 0)
    }
  } catch (error) {
    console.error('获取菜单树失败:', error)
  }
}

// 构建菜单树
const buildMenuTree = (menus: Menu[], parentId: number): Menu[] => {
  return menus
    .filter((m) => m.parent_id === parentId)
    .sort((a, b) => a.sort - b.sort)
    .map((m) => ({
      ...m,
      children: buildMenuTree(menus, m.id),
    }))
}

// 搜索
const handleSearch = () => {
  queryForm.page = 1
  getPermissionList()
}

// 重置
const handleReset = () => {
  Object.assign(queryForm, {
    page: 1,
    limit: 20,
    keyword: '',
    type: undefined,
    status: undefined,
  })
  getPermissionList()
}

// 新增权限
const handleCreate = () => {
  isEdit.value = false
  currentPermissionId.value = null
  Object.assign(form, {
    menu_id: undefined,
    name: '',
    code: '',
    type: 'api',
    description: '',
    sort: 1,
  })
  dialogVisible.value = true
}

// 编辑权限
const handleEdit = (row: Permission) => {
  isEdit.value = true
  currentPermissionId.value = row.id
  Object.assign(form, {
    menu_id: row.menu_id,
    name: row.name,
    code: row.code,
    type: row.type,
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
      if (isEdit.value && currentPermissionId.value) {
        await permissionApi.updatePermission(currentPermissionId.value, form)
        ElMessage.success('更新权限成功')
      } else {
        await permissionApi.createPermission(form)
        ElMessage.success('创建权限成功')
      }
      dialogVisible.value = false
      getPermissionList()
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

// 删除权限
const handleDelete = async (row: Permission) => {
  try {
    await ElMessageBox.confirm(`确定要删除权限"${row.name}"吗？`, '提示', {
      type: 'warning',
    })
    await permissionApi.deletePermission(row.id)
    ElMessage.success('删除成功')
    getPermissionList()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
    }
  }
}

onMounted(() => {
  getPermissionList()
  getMenuTreeOptions()
})
</script>

<style scoped lang="scss">
.permission-management {
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
