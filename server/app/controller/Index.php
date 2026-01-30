<?php

namespace app\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return '<style>*{ padding: 0; margin: 0; }</style><iframe src="https://www.thinkphp.cn/welcome?version=' . \think\facade\App::version() . '" width="100%" height="100%" frameborder="0" scrolling="auto"></iframe>';
    }

    public function hello($name = 'ThinkPHP8')
    {
        return 'hello,' . $name;
    }

    /**
     * 数据库连接测试
     */
    public function testDb()
    {
        try {
            // 获取数据库连接配置
            $config = config('database.connections.mysql');

            $result = [
                'success' => true,
                'message' => '数据库连接成功',
                'config' => [
                    'host' => $config['hostname'],
                    'port' => $config['hostport'],
                    'database' => $config['database'],
                    'charset' => $config['charset'],
                ]
            ];

            // 测试查询
            $db = \think\facade\Db::connect();
            $tables = $db->query('SHOW TABLES');

            $result['tables_count'] = count($tables);
            $result['tables'] = array_column($tables, 'tables_in_' . $config['database']);

            // 测试简单查询
            $version = $db->query('SELECT VERSION() as version');
            $result['mysql_version'] = $version[0]['version'] ?? 'unknown';

            return json($result);

        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => '数据库连接失败',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
        }
    }
}
