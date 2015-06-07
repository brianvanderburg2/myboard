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
    protected $groups = array();

    public function setValue($group, $name, $value, $lifetime=null)
    {
        if(!isset($this->groups[$group]))
        {
            $this->groups[$group] = array();
        }

        $this->groups[$group][$name] = $value;
    }

    public function getValue($group, $name, $defval=null)
    {
        return (isset($this->groups[$group]) && isset($this->groups[$group][$name])) ? 
            $this->groups[$group][$name] : $defval;
    }

    public function hasValue($group, $name)
    {
        return isset($this->groups[$group]) && isset($this->groups[$group][$name]);
    }

    public function clear($group=null)
    {
        if($group === null)
        {
            $this->groups = array();
        }
        else
        {
            $this->groups[$group] = array();
        }
    }
}

