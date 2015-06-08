<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Cache;


/**
 * This is the memory driver class.  This class will cache values only for
 * the current connection in an internal array.  As a result, value
 * timestamps are currently ignored.
 */
class Driver_memory extends Driver
{
    protected $cache = array();

    public function set($name, $value, $lifetime=null)
    {
        $this->cache[$name] = $value;
        return TRUE;
    }

    public function get($name, $defval=null)
    {
        return isset($this->cache[$name]) ? $this->cache[$name] : $defval;
    }

    public function has($name)
    {
        return isset($this->cache[$name]);
    }

    public function delete($name)
    {
        unset($this->cache[$name]);
        return TRUE;
    }

    public function flush()
    {
        $this->cache = array();
        return TRUE;
    }
}

