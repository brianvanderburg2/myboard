<?php

// File:        bootstrap.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Bootstrap file for the MyBoard application

namespace MyBoard;

// Set up an autoloader
spl_autoload_register(function($classname) {
    $ns = __NAMESPACE__ . '\\';
    if(substr($classname, 0, strlen($ns)) == $ns)
    {
        $classname = substr($classname, strlen($ns));
        $fname = __DIR__ . '/classes/' . strtolower(str_replace(array('\\', '_'), '/', $classname)) . '.php';
        if(@file_exists($fname))
        {
            include $fname;
        }
    }
});

function run($config)
{
    $board = new Board($config);
    $board->run();
}

