<?php

// File:        request.php
// Author:      Brian Allen Vanderburg Ii
// Purpose:     A simple class for parsing and handling requests

namespace MyBoard\Helper;

/**
 * The class for handling requests.
 */
class Request
{
    /**
     * Construct the request
     */
    public function __construct()
    {
        // sanitize inputs, etc
    }

    /**
     * Lazy get variables
     */
    public function __get($name)
    {
        switch($name)
        {
            case 'method':
                return $this->method = $this->getMethod();

            case 'pathinfo':
                return $this->pathinfo = $this->getPathInfo();

            case 'entry':
                return $this->entry = $this->getEntryPoint();

            default:
                Util::triggerBadGetNotice($name);
                return null;
        }
    }

    /**
     * Determine the request method
     */
    protected function getMethod()
    {
        switch($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $method = 'get';
                break;
            case 'HEAD':
                $method = 'head';
                break;
            case 'POST':
                $method = 'post';
                break;
            default:
                $method = 'unknown';
                break;
        }

        return $method;
    }

    /**
     * Determine the PATH_INFO
     */
    protected function getPathInfo($calc=FALSE)
    {
        if($calc || !array_key_exists('PATH_INFO', $_SERVER))
        {
            $pathinfo = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
            if(($pos = strpos($pathinfo, '?' . $_SERVER['QUERY_STRING'])) !== FALSE)
            {
                $pathinfo = substr($pathinfo, 0, $pos);
            }

            return $pathinfo;   
        }
        else
        {
            return $_SERVER['PATH_INFO'];
        }
    }

    /**
     * Determine the entry point used by the request.
     */
    protected function getEntryPoint()
    {
        return $_SERVER['SCRIPT_NAME'];
    }

    /**
     * Dispatch the request by including another file.
     */
    public function dispatch($dir, $params=array())
    {
        if(strlen($this->pathinfo) == 0)
            return FALSE;

        // Check each component in the path info
        $found = FALSE;
        $filename = $dir;

        $parts = explode('/', $path_info);
        if(count($parts) == 0)
            return FALSE;

        if(strlen($parts[0]) == 0) // First part is normally blank
            array_shift($parts);

        while(($part = array_shift($parts)) !== null)
        {
            // Add part to filename
            if(strlen($part) > 0 && Security::checkPathComponent($part))
            {
                $filename .= '/' . $part;
            }
            else
            {
                return FALSE;
            }

            // Check if file exists
            if(file_exists($filename . '.php'))
            {
                $found = TRUE;
                $filename = $filename . '.php';
                break;
            }
        }

        // Build remainder of parts
        if($found)
        {
            $params['pathinfo'] = implode('/', $parts);
            Util::loadPhp($filename, $params);
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }
}

