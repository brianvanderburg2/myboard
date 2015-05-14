<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework;

/**
 * This class is a simple dispatcher
 */
class Dispatcher
{
    protected $app = null;


    /**
     * Construct the attribute container.
     *
     * \param $values An associative array of attributes and value to set.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Dispatch to a certain path.
     */
    public function dispatch($request, $path)
    {
        if(count($path))
        {
            $name = 'dispatch_' . $path[0];
            if(method_exists($this, $name))
            {
                array_shift($path);
                return $this->$name($request, $path);
            }
        }

        return FALSE;
    }
}

