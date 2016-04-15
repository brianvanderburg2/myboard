<?php

// File:        template.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple php-based template system.

namespace mrbavii\Framework;

class Template
{
    private $loader = null;
    protected $app = null;
    protected $path = null;
    protected $ext = null;
    protected $current = "";
    protected $cache = array();

    public function __construct($app)
    {
        $this->loader = new PhpLoader();

        $this->app = $app;
        $this->path = $app->getConfig("template.path");
        ));

        $this->loader->setParams($app->getConfig("template.params", array()));
        $this->ext = $app->getConfig("template.ext", ".phtml");

        $this->loader->setParam("app", $this->app);
        $this->loader->setParam("template", $this);
    }
 
    public function send($template, $params=null, $override=FALSE)
    {
        print $this->get($template, $params, $override);
    }

    public function get($template, $params=null, $override=FALSE)
    {
        // Find it
        $original = $this->current;

        $norm = $this->normalize($template);
        if($norm === FALSE)
        {
            throw new Exception("Unable to normalize template: ${template} from: ${original}");
        }

        $path = $this->find($norm);
        if($path === FALSE)
        {
            throw new Exception("No such template: ${template} from: ${original}");
        }

        // Load the template
        try
        {
            $this->current = $norm;
            $result = $this->getFile($path, $params, $override);
            $this->current = $original;

            return $result;
        }
        catch(\Exception $e)
        {
            $this->current = $original;
            throw $e;
        }
    }

    public function getFile($path, $params=null, $override=FALSE)
    {
        // Note: calling getFile directly does NOT modify the current template value
        ob_start();
        try
        {
            $this->loader->loadPhp($path, $params, $override);
            return ob_get_clean();
        }
        catch(\Exception $e)
        {
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
            // Ignore NULL or empty paths paths
            if($dir === null || strlen($dir) == 0)
                continue;

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

    public function normalize($template)
    {
        $parts = explode("/", $template);

        if($parts[0] == "")
        {
            # It is absolute
            $result = array();
            array_shift($parts);
        }
        else if(strlen($this->current) == 0)
        {
            # Treated as relative to the root template namespace
            $result = array();
        }
        else
        {
            # Relative to the current template location
            $result = explode("/", $this->current);

            # Because templates are files, the path is really one less
            # Template admin/view is file view under directory admin, so
            # a relative template "header" should be admin/header not
            # admin/view/header
            array_pop($result);
        }

        # Handle '.' and '..'
        foreach($parts as $part)
        {
            if(strlen($part) == 0)
            {
                return FALSE;
            }
            else if($part == ".")
            {
                continue;
            }
            else if($part == "..")
            {
                if(count($result) > 0)
                {
                    array_pop($result);
                }
                else
                {
                    return FALSE;
                }
            }
            else
            {
                $result[] = $part;
            }
        }

        return count($result) ? implode("/", $result) : FALSE;
    }
}

