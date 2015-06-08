<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Cache;


/**
 * An APC cache driver
 */
class Driver_apc extends Driver
{
    protected $memcache = null;

    public function set($name, $value, $lifetime=null)
    {
        if($lifetime === null)
        {
            $lifetime = 0;
        }

        return apc_store($name, serialize($value), $lifetime);
    }

    public function get($name, $defval=null)
    {
        $success = FALSE;
        $result = apc_fetch($name, $success);
        return $success ? unserialize($result) : $defval;
    }

    public function has($name)
    {
        return apc_exists($name);
    }

    public function delete($name)
    {
        return apc_delete($name);
    }

    public function flush()
    {
        return apc_clear_cache("user");
    }
}

