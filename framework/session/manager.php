<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Session;

use mrbavii\Framework\Exception;

/**
 * A simple session manager class.
 */
class Manager
{
    protected $app = null;
    protected $connections = null;

    /**
     * Construct the session manager
     *
     * \param $app An instance of the application object.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a connection to a specific session driver.
     *
     * \param driver The name of the session driver.  Null to use the default.
     * \return The session connection.  If the connection is already established
     *  the existing connection will be returned.
     */
    public function connection($driver=null)
    {
        // Determine driver
        if($driver === null)
        {
            $driver = $this->app->getConfig("session.driver", "php");
        }

        // Determine if already connected
        if(!isset($this->connections[$driver]))
        {
            // Look up configuration
            $config = $this->app->getConfig("session.{$driver}", array());

            // Connect
            $config["driver"] = $driver;
            $config["app"] = $this->app; // So the driver can use request/response
            $this->connections[$driver] = $this->connect($config);
        }

        return $this->connections[$driver];
    }

    /**
     * Connect to a session driver.
     *
     * \param $config The configuration to use for connecting.
     * \return A new instance of the session connection.
     */
    public function connect($config)
    {
        // Create the connection
        if(!isset($config["driver"]))
        {
            throw new Exception("Connect error : Session configuration without driver.");
        }

        $driver = $config["driver"];
        $driver_class = __NAMESPACE__ . "\\Driver_" . $driver;
        if(class_exists($driver_class))
        {
            $instance = new $driver_class($config);
            $instance->cleanup();
            return $instance;
        }
        else
        {
            throw new Exception("Connect error : Unsupported session driver : " . $driver);
        }
    }
}

