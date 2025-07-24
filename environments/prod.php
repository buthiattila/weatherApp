<?php

return [
    'db'   => [
        'host'     => 'localhost',
        'dbname'   => 'db_name',
        'user'     => 'db_username',
        'password' => 'db_password',
    ],
    'log'  => [
        'allowedLevels' => [], // ('DEBUG', 'INFO', 'WARNING', 'ERROR') - üres esetén nem logol
        'dir'           => 'storage/logs',
        'file'          => 'app.log'
    ],
    'cron' => [
        'logFile' => 'cron.log'
    ],
];