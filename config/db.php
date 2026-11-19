<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:unix_socket=/data/mysql/mysql.sock;dbname=hemacare',
    'username' => 'root',
    'password' => 'xyc',
    'charset' => 'utf8',

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
