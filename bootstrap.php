<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * This file is the bootstrap file for the MyBoard software.
 */

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


