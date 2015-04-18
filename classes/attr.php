<?php

// File:        attr.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple attribute wrapper

namespace MyBoard;

/**
 * A simple attribute wrapper
 */
class Attr
{
    public function __construct($values)
    {
        foreach($values as $key => $value)
        {
            $this->{$key} = $value;
        }
    }
}

