<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// ============================
// 认证相关路由
// ============================
Route::group('api/auth', function () {
    // 用户登录（无需Token验证）
    Route::post('login', 'AuthController/login');

    // 用户登出（需要Token验证）
    Route::post('logout', 'AuthController/logout')->middleware(['jwt']);

    // 获取用户信息（需要Token验证）
    Route::get('userinfo', 'AuthController/getUserInfo')->middleware(['jwt']);

    // 获取用户菜单树（需要Token验证）
    Route::get('menus', 'AuthController/getUserMenus')->middleware(['jwt']);

    // 获取用户权限列表（需要Token验证）
    Route::get('permissions', 'AuthController/getUserPermissions')->middleware(['jwt']);

    // 获取用户完整信息（需要Token验证）
    Route::get('info', 'AuthController/getUserCompleteInfo')->middleware(['jwt']);
});

// ============================
// 用户管理路由
// ============================
Route::group('api/users', function () {
    // 用户 CRUD
    Route::get('/', 'UserController/index');
    Route::get('/:id', 'UserController/read');
    Route::post('/', 'UserController/save');
    Route::put('/:id', 'UserController/update');
    Route::delete('/:id', 'UserController/delete');

    // 用户角色管理
    Route::get('/:id/roles', 'UserController/getRoles');
    Route::put('/:id/roles', 'UserController/assignRoles');

    // 用户状态管理
    Route::put('/:id/status', 'UserController/updateStatus');
    Route::put('/:id/password', 'UserController/resetPassword');
})->middleware(['jwt', 'permission']);

// ============================
// 角色管理路由
// ============================
Route::group('api/roles', function () {
    // 角色 CRUD
    Route::get('/', 'RoleController/index');
    Route::get('/:id', 'RoleController/read');
    Route::post('/', 'RoleController/save');
    Route::put('/:id', 'RoleController/update');
    Route::delete('/:id', 'RoleController/delete');

    // 角色权限管理
    Route::get('/:id/permissions', 'RoleController/getPermissions');
    Route::put('/:id/permissions', 'RoleController/assignPermissions');

    // 角色菜单管理
    Route::get('/:id/menus', 'RoleController/getMenus');
    Route::put('/:id/menus', 'RoleController/assignMenus');

    // 角色用户管理
    Route::get('/:id/users', 'RoleController/getUsers');
})->middleware(['jwt', 'permission']);

// ============================
// 菜单管理路由
// ============================
Route::group('api/menus', function () {
    // 菜单 CRUD
    Route::get('/', 'MenuController/index');
    Route::get('/tree', 'MenuController/tree');
    Route::get('/:id', 'MenuController/read');
    Route::post('/', 'MenuController/save');
    Route::put('/:id', 'MenuController/update');
    Route::delete('/:id', 'MenuController/delete');
})->middleware(['jwt', 'permission']);

// ============================
// 权限管理路由
// ============================
Route::group('api/permissions', function () {
    // 权限 CRUD
    Route::get('/', 'PermissionController/index');
    Route::get('/group', 'PermissionController/group');
    Route::get('/:id', 'PermissionController/read');
    Route::post('/', 'PermissionController/save');
    Route::put('/:id', 'PermissionController/update');
    Route::delete('/:id', 'PermissionController/delete');
})->middleware(['jwt', 'permission']);

// ============================
// 测试路由
// ============================
Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

// 数据库测试路由
Route::get('testdb', 'index/testDb');
Route::get('index/testdb', 'index/testDb');
