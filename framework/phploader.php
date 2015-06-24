<?php

// File:        php.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple PHP loader classs

namespace mrbavii\Framework;

/**
 * A PHP loader loads PHP files while extracting parameters.  Nested calls to the
 * load method will merge parameters with previous calls.
 */
class PhpLoader
{
    protected $params = array();

    public function loadPhp($filename, $params=null, $override=FALSE)
    {
        $saved = null;
        if($params !== null)
        {
            $saved = $this->params;
            if($override)
            {
                $this->params = $params;
            }
            else
            {
                $this->params = array_merge($this->params, $params);
            }
        }

        try
        {
            $result = self::load($filename, $this->params);
            if($saved !== null)
            {
                $this->params = $saved;
            }
            return $result;
        }
        catch(\Exception $e)
        {
            if($saved !== null)
            {
                $this->params = $saved;
            }
            throw $e;
        }
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function setParams($values)
    {
        $this->params = array_merge($this->params, $values);
    }

    protected static function load($__filename__, $__params__)
    {
        extract($__params__);
        return require $__filename__;
    }
}

