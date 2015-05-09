<?php

// File:        bootstrap.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Bootstrap the mrbavii\Framework code

namespace mrbavii\Framework;

if(!defined(__NAMESPACE__ . '\\BOOTSTRAPPED'))
{
    define(__NAMESPACE__ . '\\BOOTSTRAPPED', TRUE);

    require_once(__DIR__ . '/classloader.php');
    ClassLoader::register(__NAMESPACE__, __DIR__);
}

