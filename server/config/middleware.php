<?php
// 中间件配置
return [
    // 别名或分组
    'alias'    => [
        // JWT认证中间件别名
        'jwt' => app\middleware\JwtAuth::class,
    ],
    // 优先级设置，此数组中的中间件会按照数组中的顺序优先执行
    'priority' => [],
];
