<?php
$container->set('config_db', function(){
    return (object)[
        'host' => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
        'database' => $_ENV['DB_DATABASE'],
        'charset' => 'utf8mb4'
    ];
});