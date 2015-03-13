<?php

// File:        template.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple php-based template system.

namespace MyBoard;

class Template
{
    protected $fn = array();
    protected $cache = array();
    protected $params = null;
    protected $userdir = null;
    protected $appdir = null;

    public function __construct($userdir, $appdir, $params=array())
    {
        $this->userdir = $userdir;
        $this->appdir = $appdir;
        $this->params = $params;
    }
 
    public function registerFunction($name, $callback)
    {
        $this->fn[$name] = $callback;
    }

    public function __call($name, $args)
    {
        if(isset($this->fn[$name]))
        {
            return call_user_func_array($this->fn[$name], $args);
        }
        else
        {
            Util::triggerCallError($name, debug_backtrace()[0]);
        }
    }

    public function send($template, $params=null, $override=FALSE)
    {
        print $this->get($template, $params, $override);
    }

    public function get($template, $params=null, $override=FALSE)
    {
        // Find it
        $path = $this->find($template);
        if($path === FALSE)
        {
            throw new Exception("No such template: ${template}");
        }

        return $this->getFile($path, $params, $override);
    }

    public function getFile($path, $params=null, $override=FALSE)
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

        // Always set $template
        $this->params['template'] = $this;

        ob_start();
        try
        {
            Util::loadPhp($path, $this->params, TRUE);

            if($saved !== null)
            {
                $this->params = $saved;
            }

            return ob_get_clean();
        }
        catch(\Exception $e)
        {
            if($saved != null)
            {
                $this->params = $saved;
            }
            ob_end_clean();

            throw $e;
        } 
    }

    public function find($template)
    {
        // Check cache first
        if(isset($this->cache[$template]))
        {
            return $this->cache[$template];
        }

        // Check user data
        $path = $this->userdir . '/' . $template . '.php';
        if(!file_exists($path))
        {
            // Check app data
            $path = $this->appdir . '/' . $template . '.php';
            if(!file_exists($path))
            {
                $path = FALSE;
            }
        }

        // Cache and return
        return $this->cache[$template] = $path;
    }
}

