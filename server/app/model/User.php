<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 用户模型
 * 负责用户相关的数据库操作
 *
 * 功能包括：
 * - 用户查询（按用户名、ID）
 * - 密码验证和加密
 * - 登录信息更新
 * - 用户信息获取
 */
class User extends Model
{
    // 表名（不包含前缀）
    protected $name = 'users';

    // 主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    // 创建时间字段名
    const CREATED_AT = 'created_at';

    // 更新时间字段名
    const UPDATED_AT = 'updated_at';

    // 隐藏字段（不序列化到JSON输出）
    protected $hidden = ['password'];

    /**
     * 根据用户名查找用户
     *
     * @param string $username 用户名
     * @return User|null 用户对象或null
     */
    public static function findByUsername(string $username): ?User
    {
        return self::where('username', $username)->find();
    }

    /**
     * 根据用户ID查找用户
     *
     * @param int $userId 用户ID
     * @return User|null 用户对象或null
     */
    public static function findById(int $userId): ?User
    {
        return self::where('id', $userId)->find();
    }

    /**
     * 验证密码
     *
     * @param string $password 明文密码
     * @param string $hash 密码哈希值
     * @return bool 验证是否成功
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 生成密码哈希
     *
     * @param string $password 明文密码
     * @return string 密码哈希值
     */
    public static function makePassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 更新用户最后登录信息
     *
     * @param int $userId 用户ID
     * @param string $ip 登录IP地址
     * @return bool 是否更新成功
     */
    public static function updateLastLogin(int $userId, string $ip): bool
    {
        return self::where('id', $userId)->update([
            'last_login_time' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ]) !== false;
    }

    /**
     * 获取用户信息（不包含敏感字段）
     *
     * @param int $userId 用户ID
     * @return array|null 用户信息数组或null
     */
    public static function getUserInfo(int $userId): ?array
    {
        $user = self::where('id', $userId)
            ->field('id,username,nickname,avatar,email,phone,role,status,last_login_time,last_login_ip,created_at,updated_at')
            ->find();

        return $user ? $user->toArray() : null;
    }
}
