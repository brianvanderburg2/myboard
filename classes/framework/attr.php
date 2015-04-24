<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace MyBoard\Framework;

/**
 * This class is a simple attribute container.
 */
class Attr
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
}

