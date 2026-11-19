<?php

// 云端（Docker/云托管）：注入了 DB_HOST 环境变量 → 走 TCP 连云库
// 本地：不设任何 DB_* 变量 → 走本机 socket
if (getenv('DB_HOST')) {
    $env = static fn(string $n, string $d = ''): string => getenv($n) ?: $d;
    return [
        'class' => \yii\db\Connection::class,
        'dsn' => sprintf('mysql:host=%s;port=%s;dbname=%s',
            $env('DB_HOST', 'localhost'), $env('DB_PORT', '3306'), $env('DB_NAME', 'hemacare')),
        'username' => $env('DB_USER', 'root'),
        'password' => $env('DB_PASSWORD'),
        'charset' => 'utf8mb4',
    ];
}

// 本地：本机 MySQL，走 socket
return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:unix_socket=/data/mysql/mysql.sock;dbname=hemacare',
    'username' => 'hemacare_user',
    'password' => 'xyc',
    'charset' => 'utf8mb4',
];
