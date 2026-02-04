# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Winter is a full-stack book/library management system built with:
- **Backend**: ThinkPHP 8 + PHP 8.0+ + MySQL
- **Frontend**: Vue 3 + TypeScript + Vite + Element Plus
- **Authentication**: JWT-based auth with automatic token injection

## Repository Structure

```
winter/
├── server/           # ThinkPHP 8 backend
│   ├── app/
│   │   ├── controller/      # Controllers
│   │   ├── middleware/      # Middleware (JwtAuth.php, Cors.php)
│   │   ├── model/           # Models
│   │   └── middleware.php   # Global middleware registration
│   ├── config/
│   │   ├── jwt.php          # JWT configuration
│   │   ├── database.php     # Database configuration
│   │   └── middleware.php   # Middleware aliases
│   ├── route/app.php        # API routes
│   ├── composer.json        # PHP dependencies
│   └── .example.env         # Environment variables template
├── web/             # Vue 3 frontend
│   ├── src/
│   │   ├── api/             # API client modules
│   │   ├── router/index.ts  # Vue Router with auth guards
│   │   ├── stores/          # Pinia state stores
│   │   ├── types/           # TypeScript definitions
│   │   ├── utils/
│   │   │   ├── request.ts   # Axios instance with interceptors
│   │   │   └── storage.ts   # LocalStorage wrapper
│   │   └── views/           # Vue components/pages
│   ├── vite.config.ts
│   ├── tsconfig.json
│   └── package.json
└── chat/            # Development notes & chat logs
```

## Chat Folder (聊天记录)

`chat/` 文件夹用于存储开发过程中的聊天记录摘要，以当天日期命名（格式：`YYYYMMDD.md`）。

**当用户要求保存聊天记录时**：
1. 检查 `chat/` 目录下是否存在以当天日期命名的文件（如 `20260205.md`）
2. 如果存在，追加内容；如果不存在，创建新文件
3. **必须包含用户的提问原文** - 在 `## 用户的提问` 部分记录用户原始需求
4. 记录解决过程、创建/修改的文件列表、功能特性等
5. 在文件末尾添加日期标记：`## 日期\n\nYYYY-MM-DD`

**文件格式示例**：
```markdown
# [主题标题]

## 用户的提问

**用户的原始提问原文**

---

## 解决过程

[详细记录解决步骤]

---

## 最终解决方案总结

[总结创建和修改的文件、功能特性等]

---

## 日期

YYYY-MM-DD
```

## Common Commands

### Backend (run from `server/` directory)

```bash
# Install dependencies
composer install

# Start development server (default: http://localhost:8000)
php think run

# Run console commands
php think <command>
```

### Frontend (run from `web/` directory)

```bash
# Install dependencies
npm install

# Start development server (with hot reload)
npm run dev

# Type check
npm run type-check

# Build for production (type-checks + builds)
npm run build

# Build only (without type-check)
npm run build-only

# Preview production build
npm run preview
```

**Node version requirement**: ^20.19.0 || >=22.12.0

## Architecture

### Authentication Flow

1. Frontend sends credentials to `POST /api/auth/login`
2. Backend validates and returns JWT token + user info
3. Frontend stores token in localStorage (key: `token`)
4. Axios request interceptor auto-injects `Authorization: Bearer {token}` header
5. Backend JwtAuth middleware validates token on protected routes
6. On 401 response, frontend clears storage and redirects to `/login`

**Key files**:
- Backend: `server/app/middleware/JwtAuth.php`, `server/config/jwt.php`
- Frontend: `web/src/utils/request.ts` (request interceptor), `web/src/router/index.ts` (route guard)

### Middleware System

**Global middleware** (applies to all routes):
- Registered in `server/app/middleware.php`
- Currently: `Cors` middleware handles all OPTIONS requests and adds CORS headers

**Route middleware** (applied per-route):
- Registered as aliases in `server/config/middleware.php`
- Applied in routes: `Route::get('url', 'controller/action')->middleware(['jwt'])`

### Frontend State Management

- **Pinia stores** use setup syntax (function-based)
- `useUserStore()` in `web/src/stores/user.ts` manages auth state
- Stores persist to localStorage via `Storage` utility

### API Configuration

- API base URL: `VITE_API_BASE_URL` env var (defaults to `http://lib.com`)
- All API calls go through `web/src/utils/request.ts` for consistent error handling
- Response format: `{ success: boolean, data: any, message: string }`

## Configuration

### Environment Variables

**Backend** (`server/.env` - copy from `.example.env`):
```
DB_HOST=127.0.0.1
DB_NAME=library
DB_USER=root
DB_PASS=root
DB_PORT=3306
JWT_SECRET=your-secret-key
JWT_EXPIRE=604800
```

**Frontend** (`web/.env` or `.env.local`):
```
VITE_API_BASE_URL=http://lib.com
```

### Database

- Default database: `library`
- Table creation script: `server/create_table.php`
- Auto-timestamps enabled (config: `auto_timestamp => true`)

## API Routes

| Method | Route | Auth Required | Description |
|--------|-------|---------------|-------------|
| POST | `/api/auth/login` | No | User login |
| GET | `/api/auth/userinfo` | Yes | Get current user info |
| POST | `/api/auth/logout` | Yes | User logout |

## Coding Conventions

### Frontend
- Vue 3 Composition API with `<script setup>` syntax
- camelCase for variables/properties, PascalCase for components
- Path alias `@/` points to `src/`
- TypeScript strict mode enabled

### Backend
- PSR-4 autoloading: `app\` namespace maps to `app/` directory
- PascalCase for classes, snake_case for database columns
- API endpoints use kebab-case (`/api/auth/userinfo`)

## Development Notes

- Test credentials: username `admin`, password `admin123`
- JWT tokens expire in 7 days by default
- CORS is enabled for all origins (development configuration)
- ThinkPHP documentation: https://doc.thinkphp.cn
