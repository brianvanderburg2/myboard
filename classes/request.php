<?php

// File:        request.php
// Author:      Brian Allen Vanderburg Ii
// Purpose:     A simple class for parsing and handling requests

namespace MyBoard;

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
                Util::triggerGetError($name);
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
}

