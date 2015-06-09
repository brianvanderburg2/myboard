<?php

// File:        template.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple php-based template system.

namespace mrbavii\Framework;

class Template
{
    protected $path = array();
    protected $params = array();
    protected $ext = ".phtml";
    protected $cache = array();

    public function __construct($path=array(), $params=array(), $ext=null)
    {
        $this->path = $path;
        $this->param = $params;
        if($ext !== null)
        {
            $this->ext = $ext;
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
        $this->params["template"] = $this;

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

        // Check the paths
        $path = FALSE;
        foreach($this->path as $dir)
        {
            // TODO: better file checks to prevent security issues
            $file = $dir . "/" . $template . $this->ext;;
            if(file_exists($file))
            {
                $path = $file;
                break;
            }
        }

        // Cache and return
        return $this->cache[$template] = $path;
    }
}

