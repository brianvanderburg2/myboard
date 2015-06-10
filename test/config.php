<?php

$config = array(
    'database.host' => '127.0.0.1',
    'database.user' => 'test',
    'database.pass' => 'test',
    'database.name' => 'test',
    
    # This will become output/test/data 
    'app.datadir.user' => __DIR__ . "/data",
    "app.error.show_user" => TRUE,

    # Password: "password" without quotes
    "admin.key" => '$2y$10$zkNHNwZ4fxkOW2OoWzWmjuMRz6TplrR1HmdGCrPcteaek9Rp7zvLS'
);

