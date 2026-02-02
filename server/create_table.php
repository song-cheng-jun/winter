<?php
// 创建用户表的脚本

$host = '127.0.0.1';
$dbname = 'library';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 创建用户表
    $sql = "CREATE TABLE IF NOT EXISTS users (
      id int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
      username varchar(50) NOT NULL COMMENT '用户名',
      password varchar(255) NOT NULL COMMENT '密码',
      nickname varchar(50) DEFAULT NULL COMMENT '昵称',
      avatar varchar(255) DEFAULT NULL COMMENT '头像',
      email varchar(100) DEFAULT NULL COMMENT '邮箱',
      phone varchar(20) DEFAULT NULL COMMENT '手机号',
      role varchar(20) DEFAULT 'user' COMMENT '角色',
      status tinyint(1) DEFAULT 1 COMMENT '状态：1正常 0禁用',
      last_login_time datetime DEFAULT NULL COMMENT '最后登录时间',
      last_login_ip varchar(50) DEFAULT NULL COMMENT '最后登录IP',
      created_at datetime DEFAULT NULL COMMENT '创建时间',
      updated_at datetime DEFAULT NULL COMMENT '更新时间',
      PRIMARY KEY (id),
      UNIQUE KEY username (username)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表'";

    $pdo->exec($sql);
    echo "用户表创建成功！\n";

    // 检查是否已存在admin用户
    $check = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'")->fetchColumn();
    if ($check == 0) {
        // 插入测试用户 (用户名: admin, 密码: admin123)
        $passwordHash = '$2y$10$LjnjdVGWoZXfVsT/SQgQouZP19rnOOyydTRPDgE9ToeKJYIMSNi52';
        $insertSql = "INSERT INTO users (username, password, nickname, role, status)
                      VALUES ('admin', ?, '管理员', 'admin', 1)";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([$passwordHash]);
        echo "测试用户创建成功！\n";
        echo "用户名: admin\n";
        echo "密码: admin123\n";
    } else {
        echo "admin 用户已存在，跳过插入。\n";
    }

} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
