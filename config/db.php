<?php

$env = static function (string $name, string $default = ''): string {
    $value = getenv($name);

    return $value === false || $value === '' ? $default : $value;
};

return [
    'class' => \yii\db\Connection::class,
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        $env('DB_HOST', 'localhost'),
        $env('DB_PORT', '3306'),
        $env('DB_NAME', 'yii2basic')
    ),
    'username' => $env('DB_USER', 'root'),
    'password' => $env('DB_PASSWORD'),
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
