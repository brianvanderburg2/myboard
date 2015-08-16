<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Session;
use mrbavii\Framework;

/**
 * The base session driver.
 */
abstract class Driver
{
    const timed_key = '_mrbavii_framework_session_timed_';
    protected $config = null;
    protected $timed_duration = 600;

    public function __construct($config)
    {
        $this->config = $config;

        if(isset($config['timed.duration']))
        {
            $this->timed_duration = $config['timed.duration'];
        }
    }
    
    abstract public function close();
    abstract public function destroy();
    abstract public function set($name, $value);
    abstract public function get($name, $defval=null);
    abstract public function delete($name);
    abstract public function has($name);

    public function cleanup()
    {
        // Remove any timed variables that are expired
        $timestamp = time() - intval($this->timed_duration);
        $timed = $this->get(self::timed_key, array());

        $modified = FALSE;
        foreach(array_keys($timed) as $name)
        {
            if($timed[$name] < $timestamp || !$this->has($name))
            {
                $this->delete($name);
                unset($timed[$name]);
                $modified = TRUE;
            }
        }

        if($modified)
        {
            $this->set(self::timed_key, $timed);
        }
    }
    
    public function create()
    {
        for($count = 0; $count < 10000; $count++)
        {
            $name = str_replace('-', '', Framework\Util::guid());
            if(!$this->check($name))
            {
                $this->set($name, FALSE);
                return $name;
            }
        }

        throw new Exception('Unable to create unique session variable.');
    }

    public function expire($name)
    {
        $timed = $this->get(self::timed_key, array());
        $timed[$name] = time();
        $this->set(self::timed_key, $timed);
    }
}

