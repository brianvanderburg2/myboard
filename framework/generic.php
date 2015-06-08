<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework;

/**
 * This class is a generic attribute container.
 */
class Generic
{
    /**
     * Construct the attribute container.
     *
     * \param $values An associative array of attributes and value to set.
     */
    public function __construct($values=array())
    {
        foreach($values as $key => $value)
        {
            $this->{$key} = $value;
        }
    }

    /**
     * Call a method if an attribute is set as a method.
     *
     * \param $method The name of the method to call.
     * \param $args The arguments to pass to the method.
     * \return The return value of the method call.
     */
    public function __call($method, $args)
    {
        if(isset($this->{$method}) && is_callable($this->{$method}))
        {
            // First arg is this
            $args = array_merge(array($this), $args);
            return call_user_func_array($this->{$method}, $args);
        }
        else
        {
            $cls = __CLASS__;
            throw new Exception("Fatal Error: Call to undefined method {$cls}::{$method}()");
        }
    }
}

