<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework;

/**
 * This class is a base dispatcher.
 */
class Dispatcher
{
    protected $app;

    /**
     * Construct the dispatcher.
     *
     * \param $app The application using the dispatcher.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Execute the dispatcher.
     */
    public function dispatch($request, $path)
    {
        // Determine target function
        $target = array_shift($path);
        if($target === null || $target == "")
        {
            $target = "index";
        }
        
        // Determine if the method exists and call it
        $method = "dispatch_" . $target;
        if(method_exists($this, $method))
        {
            return call_user_func([$this, $method], $request, $path);
        }
        
        // If we got here, the method was not found, just return
        return;
    }
}

