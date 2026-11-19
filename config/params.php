<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    // 微信小程序（患者端）配置 —— 从环境变量读取，勿硬编码
    'wechat' => [
        'appid' => getenv('WECHAT_APPID') ?: '',
        'secret' => getenv('WECHAT_SECRET') ?: '',
    ],
];
