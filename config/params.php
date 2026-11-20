<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    // 微信小程序（患者端）配置
    // 云端（Docker/云托管）：环境变量存在则优先（与 db.php 同一模式）
    // 本地（开发机直跑）：环境变量缺失时用下方写死值，改完重启本地服务生效
    'wechat' => [
        'appid' => getenv('WECHAT_APPID') ?: 'wxa8e7b4632634d2e0',
        'secret' => getenv('WECHAT_SECRET') ?: '724afe4f9921ea4875e6f95cea850c93',
    ],
];
