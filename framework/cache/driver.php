<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Cache;


/**
 * This is the base driver class.
 */
abstract class Driver
{
    use \mrbavii\Framework\Traits\Attr;

    protected $config = null;

    /**
     * Construct the cache driver
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Set a value for a specific time.
     *
     * \param $name The name to give the item
     * \param $value The value of the item.
     * \param $lifetime The lifetime of the item in seconds.
     * \return TRUE on success, FALSE on failure.
     */
    abstract public function set($name, $value, $lifetime=null);

    /**
     * Get the value for a specific item.
     *
     * \param $name The name of the item.
     * \param $defval The default value fo the item.
     * \return The cache value if set, or the default value if not set or expired.
     */
    abstract public function get($name, $defval=null);

    /**
     * Determine if a value exists.
     *
     * \param $name The name of the item.
     * \return TRUE if the item exists, otherwise FALSE.
     */
     abstract public function has($name);

     /**
      * Clear a value.
      *
      * \param $name The name of valuue to clear.
      * \return TRUE on success, FALSE on failure
      */
    abstract public function delete($name);

    /**
     * Flush all values.
     * \return TRUE on success, FALSE on failure
     */
    abstract public function flush();


}

