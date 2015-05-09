<?php

// File:        bootstrap.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Bootstrap the mrbavii\MyBoard code

namespace mrbavii\MyBoard;

use mrbavii\Framework;

if(!defined(__NAMESPACE__ . '\\BOOTSTRAPPED'))
{
    define(__NAMESPACE__ . '\\BOOTSTRAPPED', TRUE);

    require __DIR__ . '/../framework/bootstrap.php';
    Framework\ClassLoader::register(__NAMESPACE__, __DIR__);
}

