<?php

// File:        Base.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Base Facade class

namespace mrbavii\Framework;

/**
 * Base facade class
 *
 * The name "facade" is used from laravel but the code is not.  Originally
 * the idea was to name it "static" but that is a reserved keyword.
 */
class Facade
{
    /**
     * Return an instance of the class to operate on
     */
    protected static function getFacadeInstance()
    {
        // TODO: throw an exception.  This must be implemented in derived
        // classes
    }

    /**
     * Call a method of a class
     */
    public static function __callStatic($name, $arguments)
    {
        // TODO: check to verify teh function exists
        return call_user_func_array(array(static::getFacadeInstance(), $name), $arguments);
    }
}

