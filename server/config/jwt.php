<?php
declare (strict_types = 1);

/**
 * JWT配置文件
 * 用于JWT Token的生成和验证
 *
 * 使用说明：
 * 1. secret: JWT密钥，生产环境请使用复杂的随机字符串
 * 2. expire: Token过期时间（秒），默认7天（604800秒）
 * 3. iss: 签发者标识
 * 4. aud: 受众标识
 */
return [
    // JWT密钥（生产环境请使用复杂的随机字符串）
    'secret' => env('JWT_SECRET', 'library-system-secret-key-2026'),

    // Token过期时间（秒），默认7天
    'expire' => env('JWT_EXPIRE', 604800),

    // 签发者
    'iss' => env('JWT_ISS', 'library-system'),

    // 受众
    'aud' => env('JWT_AUD', 'library-app'),
];
