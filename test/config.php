<?php

$config = array(
    'database.default' => 'myboard',
    'database.connections.myboard' => array(
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'username' => 'test',
        'password' => 'test',
        'dbname' => 'test',
    ),
    
    # This will become output/test/data 
    'app.datadir.user' => __DIR__ . "/data",
    "app.error.show_user" => TRUE,

    # Password: "password" without quotes
    "admin.key" => '$2y$10$zkNHNwZ4fxkOW2OoWzWmjuMRz6TplrR1HmdGCrPcteaek9Rp7zvLS'
);

