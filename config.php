<?php

return [
    'db' => [
        'type' => 'oracle', // or 'sqlsrv'
        'username' => 'db_user',
        'password' => 'db_pass',
        'connection_string' => 'localhost/XEPDB1'
    ],

    'api' => [
        'base_url' => 'https://api.aviritech.com', /* Edit as required*/
        'token' => 'YOUR_SECURE_TOKEN'
    ],

    'paths' => [
        'queue' => __DIR__ . '/queue/',
        'logs' => __DIR__ . '/logs/'
    ]
];
