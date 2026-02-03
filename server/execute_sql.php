<?php
require __DIR__ . '/vendor/autoload.php';

try {
    // 读取 SQL 文件
    $sql = file_get_contents(__DIR__ . '/create_rbac_tables.sql');

    // 连接数据库
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=library', 'root', 'root');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 执行 SQL
    $pdo->exec($sql);

    echo "权限功能模块数据表创建成功！\n";

    // 统计数据
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    echo "角色数量: " . $stmt->fetch(PDO::FETCH_ASSOC)['count'] . "\n";

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM menus");
    echo "菜单数量: " . $stmt->fetch(PDO::FETCH_ASSOC)['count'] . "\n";

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM permissions");
    echo "权限数量: " . $stmt->fetch(PDO::FETCH_ASSOC)['count'] . "\n";

} catch (PDOException $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
