<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Cache;

use mrbavii\Framework\Exception;

/**
 * Cache management class
 */
class Manager
{
    protected $app = null;
    protected $connections = array();

    /**
     * Construct the cache manager
     *
     * \param $app An instance of the application object.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a connection to a specific cache driver.
     *
     * \param driver The name of the cache driver.  Null to use the default.
     * \return The cache connection.  If the connection is already established
     *  the existing connection will be returned.
     */
    public function connection($driver=null)
    {
        // Determine the cache driver
        if($driver === null)
        {
            $driver = $this->app->getConfig("cache.driver", "memory");
        }

        // Determine if already connected
        if(!isset($this->connections[$driver]))
        {
            // Look up configuration
            $config = $this->app->getConfig("cache." . $driver, array());
        
            // Connect and store
            $config["driver"] = $driver;
            $this->connections[$driver] = $this->connect($config);
        }

        // Return the connection
        return $this->connections[$driver];
    }

    /**
     * Connect to a cache driver
     * \param $config The configuration to use to connect.
     * \return A new instance of the cache connection.
     */
    public function connect($config)
    {
        // Create the connection
        if(!isset($config["driver"]))
        {
            throw new Exception("Connect error : Cache configuration without driver");
        }

        $driver = $config["$driver"];
        $driver_class  __NAMESPACE__ . "\\Driver_" . $driver;
        if(class_exists($driver_class))
        {
            return new $driver_class($config);
        }
        else
        {
            throw new Exception("Connect error : Unsupported cache driver : " . $driver);
        }
    }
}
