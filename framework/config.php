<?php

// File:        config.php
// Author:      Brian Allen Vanderburg II
// Purpose:     General and framework configuration

namespace mrbavii\Framework;

/**
 * Configuration class to hold configuration value for general use as well as 
 * framework specific configuration values.  Other objects in the framework
 * may require an instance of this class to be passed to it.
 */
class Config implements \ArrayAccess
{
    protected $_config = array();

    /**
     * Construct the configuration
     *
     * \param ...$config Configuration items, merged in left to right.
     */
    public function __construct()
    {
        $this->_config = array();
        $configs = func_get_args();
        foreach($configs as $config)
        {
            if($config instanceof Config)
            {
                $this->_config = array_merge($this->_config, $config->_config);
            }
            else
            {
                $this->_config = array_merge($this->_config, $config);
            }
        }
    }

    /** 
     * Get a configuration value.
     *
     * \param $name The name of the configuration value to get.
     * \param $def The default value if the configuration value is not set.
     */
    public function get($name, $def=null)
    {
        if(isset($this->_config[$name]))
        {
            return $this->_config[$name];
        }
        else
        {
            return $def;
        }
    }

    /**
     * Merge in configuration
     *
     * \param $config The configuration values to merge.
     */
    public function merge($config)
    {
        if($config instanceof Config)
        {
            $this->_config = array_merge($this->_config, $config->_config);
        }
        else
        {
            $this->_config = array_merge($this->_config, $config);
        }
    }

    /* Methods for ArrayAccess */

    /**
     * Test if a configuration value exists.
     */
    public function offsetExists($offset)
    {
        return isset($this->_config[$offset]);
    }

    /**
     * Get a configuration value.
     */
    public function offsetGet($offset)
    {
        return $this->_config[$offset];
    }

    /**
     * Set a configuration value.
     */
    public function offsetSet($offset, $value)
    {
        $this->_config[$offset] = $value;
    }

    /**
     * Remove a configuration value.
     */
    public function offsetUnset($offset)
    {
        unset($this->_config[$offset]);
    }
}

