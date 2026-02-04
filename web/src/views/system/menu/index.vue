<template>
  <div class="menu-management">
    <el-card shadow="never">
      <!-- 操作按钮 -->
      <div class="toolbar">
        <el-button type="primary" @click="handleCreate">
          <el-icon><Plus /></el-icon>
          新增菜单
        </el-button>
        <el-button @click="handleExpandAll">展开全部</el-button>
        <el-button @click="handleCollapseAll">折叠全部</el-button>
      </div>

      <!-- 菜单树表格 -->
      <el-table
        v-loading="loading"
        :data="tableData"
        row-key="id"
        :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
        border
        stripe
        default-expand-all
        ref="tableRef"
      >
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="菜单名称" min-width="200" />
        <el-table-column prop="name" label="路由名称" width="150" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getTypeTagType(row.type)">
              {{ getTypeName(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="path" label="路由路径" min-width="180" />
        <el-table-column prop="component" label="组件路径" min-width="180" />
        <el-table-column prop="icon" label="图标" width="100" />
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="260" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button
              v-if="row.type !== 'button'"
              link
              type="primary"
              size="small"
              @click="handleAddChild(row)"
            >
              新增子菜单
            </el-button>
            <el-button link type="danger" size="small" @click="handleDelete(row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 新增/编辑菜单对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      @close="handleDialogClose"
    >
      <el-form ref="formRef" :model="form" :rules="formRules" label-width="100px">
        <el-form-item label="上级菜单" prop="parent_id">
          <el-tree-select
            v-model="form.parent_id"
            :data="menuTreeOptions"
            :props="{ value: 'id', label: 'title', children: 'children' }"
            placeholder="请选择上级菜单"
            check-strictly
            clearable
          />
        </el-form-item>
        <el-form-item label="菜单类型" prop="type">
          <el-radio-group v-model="form.type">
            <el-radio value="directory">目录</el-radio>
            <el-radio value="menu">菜单</el-radio>
            <el-radio value="button">按钮</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="菜单名称" prop="title">
          <el-input v-model="form.title" placeholder="请输入菜单名称" />
        </el-form-item>
        <el-form-item label="路由名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入路由名称" />
        </el-form-item>
        <el-form-item label="路由路径" prop="path">
          <el-input v-model="form.path" placeholder="请输入路由路径，如：/system/user" />
        </el-form-item>
        <el-form-item label="组件路径" prop="component">
          <el-input
            v-model="form.component"
            placeholder="请输入组件路径，如：system/user/index"
          />
        </el-form-item>
        <el-form-item label="重定向" prop="redirect">
          <el-input v-model="form.redirect" placeholder="请输入重定向路径" />
        </el-form-item>
        <el-form-item label="图标" prop="icon">
          <el-input v-model="form.icon" placeholder="请输入图标名称" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="form.sort" :min="0" />
        </el-form-item>
        <el-form-item label="是否隐藏">
          <el-switch v-model="form.hidden" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="总是显示">
          <el-switch v-model="form.always_show" :active-value="1" :inactive-value="0" />
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
import { menuApi } from '@/api'
import type { Menu, MenuForm } from '@/types'

// 表格数据
const tableData = ref<Menu[]>([])
const loading = ref(false)
const tableRef = ref()

// 菜单树选项（用于选择上级菜单）
const menuTreeOptions = ref<Menu[]>([])

// 对话框相关
const dialogVisible = ref(false)
const dialogTitle = computed(() => (isEdit.value ? '编辑菜单' : '新增菜单'))
const isEdit = ref(false)
const currentMenuId = ref<number | null>(null)
const parentId = ref<number | undefined>(undefined)
const submitLoading = ref(false)

// 表单数据
const form = reactive<MenuForm>({
  parent_id: 0,
  name: '',
  type: 'menu',
  path: '',
  component: '',
  redirect: '',
  icon: '',
  title: '',
  hidden: 0,
  always_show: 0,
  sort: 1,
})
const formRef = ref<FormInstance>()
const formRules: FormRules = {
  title: [{ required: true, message: '请输入菜单名称', trigger: 'blur' }],
  name: [{ required: true, message: '请输入路由名称', trigger: 'blur' }],
  type: [{ required: true, message: '请选择菜单类型', trigger: 'change' }],
}

// 获取类型名称
const getTypeName = (type: string) => {
  const map: Record<string, string> = {
    directory: '目录',
    menu: '菜单',
    button: '按钮',
  }
  return map[type] || type
}

// 获取类型标签类型
const getTypeTagType = (type: string) => {
  const map: Record<string, any> = {
    directory: 'warning',
    menu: 'primary',
    button: 'info',
  }
  return map[type] || ''
}

// 获取菜单列表
const getMenuList = async () => {
  loading.value = true
  try {
    const response = await menuApi.getMenuList({ status: undefined })
    if (response.success && response.data) {
      tableData.value = buildMenuTree(response.data, 0)
    }
  } catch (error) {
    console.error('获取菜单列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 获取菜单树选项
const getMenuTreeOptions = async () => {
  try {
    const response = await menuApi.getMenuTree()
    if (response.success && response.data) {
      // 添加根节点选项
      menuTreeOptions.value = [
        { id: 0, title: '根目录', children: buildMenuTree(response.data, 0) } as Menu,
      ]
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

// 新增菜单
const handleCreate = () => {
  isEdit.value = false
  currentMenuId.value = null
  parentId.value = 0
  Object.assign(form, {
    parent_id: 0,
    name: '',
    type: 'menu',
    path: '',
    component: '',
    redirect: '',
    icon: '',
    title: '',
    hidden: 0,
    always_show: 0,
    sort: 1,
  })
  dialogVisible.value = true
}

// 新增子菜单
const handleAddChild = (row: Menu) => {
  isEdit.value = false
  currentMenuId.value = null
  parentId.value = row.id
  Object.assign(form, {
    parent_id: row.id,
    name: '',
    type: 'menu',
    path: '',
    component: '',
    redirect: '',
    icon: '',
    title: '',
    hidden: 0,
    always_show: 0,
    sort: 1,
  })
  dialogVisible.value = true
}

// 编辑菜单
const handleEdit = (row: Menu) => {
  isEdit.value = true
  currentMenuId.value = row.id
  parentId.value = row.parent_id
  Object.assign(form, {
    parent_id: row.parent_id,
    name: row.name,
    type: row.type,
    path: row.path || '',
    component: row.component || '',
    redirect: row.redirect || '',
    icon: row.icon || '',
    title: row.title,
    hidden: row.hidden,
    always_show: row.always_show,
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
      if (isEdit.value && currentMenuId.value) {
        await menuApi.updateMenu(currentMenuId.value, form)
        ElMessage.success('更新菜单成功')
      } else {
        await menuApi.createMenu(form)
        ElMessage.success('创建菜单成功')
      }
      dialogVisible.value = false
      getMenuList()
      getMenuTreeOptions()
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

// 展开全部
const handleExpandAll = () => {
  // 这里可以使用表格的展开方法，或者通过设置 default-expand-all 来实现
  getMenuList()
}

// 折叠全部
const handleCollapseAll = () => {
  // 重新获取数据并设置不展开
  // ElTable 的树形表格默认是展开的，需要手动控制
  tableData.value = []
  setTimeout(() => {
    getMenuList()
  }, 0)
}

// 删除菜单
const handleDelete = async (row: Menu) => {
  // 检查是否有子菜单
  if (row.children && row.children.length > 0) {
    ElMessage.warning('该菜单下有子菜单，无法删除')
    return
  }

  try {
    await ElMessageBox.confirm(`确定要删除菜单"${row.title}"吗？`, '提示', {
      type: 'warning',
    })
    await menuApi.deleteMenu(row.id)
    ElMessage.success('删除成功')
    getMenuList()
    getMenuTreeOptions()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
    }
  }
}

onMounted(() => {
  getMenuList()
  getMenuTreeOptions()
})
</script>

<style scoped lang="scss">
.menu-management {
  .toolbar {
    margin-bottom: 20px;
  }
}
</style>
