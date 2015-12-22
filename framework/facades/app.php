<?php

// File:        app.php
// Author:      Brian Allen Vanderburg II
// Purpose:     App static class

namespace mrbavii\Framework\Facades;
use mrbavii\Framework\Facade;
use mrbavii\Framework\App as _App;

/**
 * App static class
 */
class App extends Facade
{
    protected static function getFacadeInstance()
    {
        return _App::instance();
    }
}

