<?php

error_reporting(0);
define('ROOT', dirname(__FILE__));
define('DIVIDER', str_repeat('-', 80)."\n");
$env = parse_ini_file(ROOT.'/.env', false, INI_SCANNER_RAW);

function console($item, $result, $err = '')
{
    $bg = $err ? '41' : '42';
    $result = "\e[{$bg}m\e[37m $result \e[0m\e[0m";
    echo sprintf("%s:%s %s\n", $item, $result, $err);
}

echo "\n".DIVIDER."[ 环境检查 ]\n";

// PHP 版本
$err = version_compare(PHP_VERSION, '7.3.0', '>=') ? '' : 'PHP 版本需 >= 7.3.0';
console('PHP 版本', PHP_VERSION, $err);

// PDO 扩展
$pdo = class_exists('pdo');
$err = $pdo ? '' : '请先安装 PHP PDO 扩展';
console('PDO 扩展', $pdo ? '已安装' : '未安装', $err);

// Laravel 密钥
$err = $env['APP_KEY'] ? '' : '请执行命令 php artisan key:generate 生成密钥';
console('Laravel 密钥', $env['APP_KEY'] ? '已生成' : '未生成', $err);

// Storage 目录
$writable = is_writable(ROOT.'/storage');
$err = $writable ? '' : '请设置 storage 目录为可读写';
console('Storage 目录', $writable ? '可读写' : '不可读写', $err);

// Composer Package
$import = file_exists(ROOT.'/vendor/autoload.php');
$err = $import ? '' : '请安装 Composer 并执行 composer install 安装包';
console('Composer Package', $import ? '已导入' : '未导入', $err);

// MySQL 连接
try {
    $err = '';
    $dsn = "mysql:host={$env['DB_HOST']}:{$env['DB_PORT']};dbname={$env['DB_DATABASE']}";
    $db = new PDO($dsn, $env['DB_USERNAME'], $env['DB_PASSWORD'], [PDO::ATTR_TIMEOUT => 3]);
} catch (Exception $e) {
    $err = $e->getMessage();
}
console('MySQL 连接', $err ? '失败' : '成功', $err);

// MySQL 数据表
try {
    if ($err) {
        throw new Exception($err);
    }
    $err = '';
    $tables = $db->query("show tables like 'code_leak'")->fetchAll(PDO::FETCH_ASSOC)[0];
    if (!count($tables)) {
        throw new Exception('请执行 php artisan migrate 导入数据表');
    }
} catch (Exception $e) {
    $err = $e->getMessage();
}
console('MySQL 数据表', $err ? '未导入' : '已导入', $err);

echo DIVIDER."[ 其他信息 ]\n";

// 码小六版本
$version = trim(file_get_contents(ROOT.'/version'));
echo "码小六版本：$version\n";

// 框架运行环境
$appEnv = $env['APP_ENV'] ?? '无';
echo "框架运行环境：$appEnv\n";

// 框架调试开关
$appDebug = $env['APP_DEBUG'] ?? '无';
echo "框架调试开关：$appDebug\n";

// PHP 禁用函数
$disFuns = get_cfg_var('disable_functions') ?: '无';
echo "PHP 禁用函数：$disFuns\n";

// PHP 已编译模块
$exts = implode(',', get_loaded_extensions());
echo "PHP 已编译模块：$exts\n";

echo DIVIDER."\n有任何问题和建议请联系-> https://github.com/4x99/code6/issues\n\n";
