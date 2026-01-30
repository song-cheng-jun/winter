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
});

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
