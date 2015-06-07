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
     * \param $group The group to define the item in.
     * \param $name The name to give the item
     * \param $value The value of the item.
     * \param $lifetime The lifetime of the item in seconds.
     */
    abstract public function setValue($group, $name, $value, $lifetime=null);

    /**
     * Get the value for a specific item.
     *
     * \param $group The group of the item.
     * \param $name The name of the item.
     * \param $defval The default value fo the item.
     * \return The cache value if set, or the default value if not set or expired.
     */
    abstract public function getValue($group, $name, $defval=null);

    /**
     * Determine if a value exists.
     *
     * \param $group The group of the item.
     * \param $name The name of the item.
     * \return TRUE if the item exists, otherwise FALSE.
     */
     abstract public function hasValue($group, $name);

     /**
      * Clear a group of values or all values.
      *
      * \param $group The group of valuues to clear.  If a driver
      *  can not clear only values of that group, it should clear
      *  all values.  If not specified, all values should be cleared.
      */
    abstract public function clear($group=null);
}

