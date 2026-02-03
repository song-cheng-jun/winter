# Claude Code 指令大全

本文档列出 Claude Code (claude.ai/code) 中常用的指令和功能。

## 斜杠指令 (Slash Commands)

### /init
初始化代码库，生成或更新 `CLAUDE.md` 文件，帮助 Claude 了解项目结构。

### /help
显示帮助信息和可用指令列表。

### /clear
清空当前对话历史，开始新的对话。

### /commit
创建 Git 提交。Claude 会：
1. 查看 git 状态和差异
2. 分析变更内容
3. 生成提交信息
4. 执行提交

```
用法: /commit [-m "提交信息"]
```

### /diff
显示当前代码的变更（git diff）。

### /patch
应用补丁或代码修改到文件。

### /read
读取指定文件内容。

```
用法: /read <文件路径>
```

### /write
写入或覆盖文件内容。

```
用法: /write <文件路径>
```

### /edit
编辑文件的特定部分。

```
用法: /edit <文件路径>
```

### /search
在代码库中搜索内容。

```
用法: /search <关键词>
```

### /run
运行终端命令。

```
用法: /run <命令>
```

### /test
运行测试套件。

```
用法: /test [测试文件/测试名称]
```

### /lint
运行代码检查工具。

### /format
格式化代码。

### /build
构建项目。

### /deploy
部署项目。

## 对话技巧

### 引用文件
直接提及文件名或路径，Claude 会自动读取：
```
"请看看 src/main.ts 文件"
```

### 引用代码
复制粘贴代码块，Claude 会分析：
```
这段代码有什么问题？
<粘贴代码>
```

### 上下文引用
使用 `@` 符号引用特定内容：
```
@web/src/utils/request.ts 中的请求拦截器
```

## 技能 (Skills)

项目可通过技能系统扩展功能。使用方式：

```
<技能名>: <参数>
```

例如：
```
pdf: convert document.pdf
review-pr: 123
```

## 配置文件

### .claudeignore
指定 Claude 应忽略的文件/目录：

```
# 忽略依赖
node_modules/
vendor/

# 忽略构建产物
dist/
build/

# 忽略敏感文件
.env
*.key
```

### CLAUDE.md
项目级配置文件，包含：
- 项目架构说明
- 常用命令
- 编码规范
- 开发指南

## 最佳实践

1. **善用 /commit**：让 Claude 帮你生成规范的提交信息
2. **使用 /init**：新项目先运行，让 Claude 快速了解代码库
3. **引用具体文件**：直接说文件名，比描述路径更高效
4. **分步执行**：复杂任务让 Claude 分步骤完成
5. **利用 .claudeignore**：避免读取不必要的文件

## 快捷键

- `Ctrl + C`：中断当前操作
- `Ctrl + D`：结束对话
- `↑ / ↓`：浏览历史命令

## 获取更多帮助

- GitHub Issues: https://github.com/anthropics/claude-code/issues
- 官方文档: https://claude.ai/code
