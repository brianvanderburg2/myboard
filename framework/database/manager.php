<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework\Database;

/**
 * A database manager class
 */
class Manager
{
    protected $app = null;
    protected $connections = array();

    /**
     * Construct the database manager.
     *
     * \param $app An instance of the application object.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a connection to a named database
     *
     * \param name The named database to connect to, null to use the default.
     * \return The database connection.  If the named connection is already
     *  connected, the existing connection will be returned.
     */
    public function connection($name=null)
    {
        // Determine default if needed
        if($name === null)
        {
            $name = $this->app->getConfig('database.default');
            if($name === null)
            {
                throw new Exception('Connect error : No default connection');
            }
        }

        // Determine if already connected
        if(!isset($this->connections[$name]))
        {
            // Look up the config
            $config = $this->app->getConfig('database.connections.' . $name);
            if($config === null)
            {
                throw new Exception('Connect error : No named connection : ' . $name);
            }

            // Connect and store
            $this->connections[$name] = $this->connect($config);
        }

        // Return the connection
        return $this->connections[$name];
    }


    /**
     * Connect to a database.
     *
     * \param $config The configuration to use to connect.
     * \return A new instance of the connection.
     */
    public function connect($config)
    {
        // Create the connection
        if(!isset($config['driver']))
        {
            throw new Exception('Connect error : Database configuration without driver');
        }
        
        $driver = $config['driver'];
        $driver_class = __NAMESPACE__ . '\\Driver_' . $driver;
        if(class_exists($driver_class))
        {
            return new $driver_class($this->app, $config);
        }
        else
        {
            throw new Exception('Connect error : Unsupported driver : ' . $driver);
        }
    }
}




