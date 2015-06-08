<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Cache;


/**
 * A memcache driver
 */
class Driver_memcache extends Driver
{
    protected $memcache = null;

    public function __construct($config)
    {
        parent::__construct($config);

        if(isset($config["unix_socket"]))
        {
            $host = "unix://{$config["unix_socket"]}";
            $port = 0;
        }
        else
        {
            $host = isset($config["host"]) ? $config["host"] : "localhost";
            $port = isset($config["port"]) ? $config["port"] : 11211;
        }

        $this->memcache = new Memcached;
        $this->memcache->setOption(Memcached::OPT_BINARY_PROTOCOL, TRUE);
        $this->memcache->addServer($host, $port);
    }

    public function set($name, $value, $lifetime=null)
    {
        if($lifetime === null)
        {
            $lifetime = 0;
        }
        else if($lifetime > 2592000) // 30 days
        {
            $lifetime = 2592000;
        }

        return $this->memcache->set($name, $value, $lifetime);
    }

    public function get($name, $defval=null)
    {
        $result = $this->memcache->get($name);
        return ($result === FALSE) ? $defval : null;
    }

    public function has($name)
    {
        return $this->memcache->get($name) !== FALSE;
    }

    public function delete($name)
    {
        return $this->memcache->delete($name);
    }

    public function flush()
    {
        return $this->memcache->flush();
    }
}

