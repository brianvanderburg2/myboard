<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * A request object handles basic parsing of requests.
 */

namespace mrbavii\Framework;

/**
 * The class for handling requests.
 */
class Request
{
    protected $_pathinfo = null; /**< \brief The PATHINFO of the request */
    protected $_path = null; /**< \brief The array of path parts. */
    protected $_method = null; /**< \brief The method of the request.  \see getMethod */
    protected $_params = array(); /**< User parameters. */


    /**
     * Construct the request
     *
     */
    public function __construct()
    {
    }

    /**
     * Get the PATH_INFO of the request or forcefully set the PATH_INFO.
     */
    public function pathinfo($pathinfo=null)
    {
        if($pathinfo !== null)
        {
            // Forcefully set pathinfo, reset path
            $this->_pathinfo = $pathinfo;
            $this->_path = null;
        }
        else if($this->_pathinfo === null)
        {
            if(!array_key_exists("PATH_INFO", $_SERVER))
            {
                $this->_pathinfo = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["SCRIPT_NAME"]));
                if(($pos = strpos($this->_pathinfo, "?")) !== FALSE)
                {
                    $this->_pathinfo = substr($this->_pathinfo, 0, $pos);
                }
            }
            else
            {
                $this->_pathinfo = $_SERVER["PATH_INFO"];
            }
        }

        return $this->_pathinfo;
    }

    /**
     * Get the PATH_INFO of the request in an array form with the leading empty element removed.
     */
    public function path()
    {
        if($this->_path === null)
        {
            $this->_path = explode("/", $this->pathinfo());
            if(count($this->_path) && strlen($this->_path[0]) == 0)
            {
                array_shift($this->_path); // remove first part since path info starts with "/"
            }
        }

        return $this->_path;
    }

    /**
     * Get the method of the request.
     */
    public function method()
    {
        if($this->_method === null)
        {
            switch($_SERVER["REQUEST_METHOD"])
            {
                case "GET":
                    $this->_method = "get";
                    break;
                case "HEAD":
                    $this->_method = "head";
                    break;
                case "POST":
                    $this->_method = "post";
                    break;
                default:
                    $this->_method = "unknown";
                    break;
            }
        }

        return $this->_method;
    }

    /**
     * Get the path entry point.
     */
    public function entry()
    {
        return $_SERVER["SCRIPT_NAME"];
    }

    /**
     * Get but without any query string.
     */
    public function url()
    {
        return $this->getEntry() . $this->getPathInfo();
    }

    /**
     * Get the URI
     */
    public function uri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Set a user parameter.
     */
    public function set($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * Get a user parameter.
     */
    public function param($name=null, $defval=null)
    {
        if($name === null)
        {
            return $this->_params;
        }
        return isset($this->_params[$name]) ? $this->_params[$name] : $defval;
    }


    /**
     * Get a $_GET variable.
     */
    public function query($name=null, $defval=null)
    {
        if($name === null)
        {
            return $_GET;
        }
        return isset($_GET[$name]) ? $_GET[$name] : $defval;
    }

    /**
     * Get a $_POST variable.
     */
    public function post($name=null, $defval=null)
    {
        if($name === null)
        {
            return $_POST;
        }
        return isset($_POST[$name]) ? $_POST[$name] : $defval;
    }

    /**
     * Get a $_COOKIE variable.
     */
    public function cookie($name=null, $defval=null)
    {
        if($name === null)
        {
            return $_COOKIE;
        }
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $defval;
    }

    /**
     * Get a $_SERVER variable
     */

    public function server($name=null, $defval=null)
    {
        if($name === null)
        {
            return $_SERVER;
        }
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $defval;
    }

    /**
     * Get a $_ENV variable
     */

    public function env($name=null, $defval=null)
    {
        if($name === null)
        {
            return $_ENV;
        }
        return isset($_ENV[$name]) ? $_ENV[$name] : $defval;
    }

    /**
     * Get a paramter in specific order
     * param, get, post, cookie, server, env
     */
    public function get($name, $defval=null)
    {
        if(isset($this->_params[$name]))
        {
            return $this->_params[$name];
        }
        else if(isset($_GET[$name]))
        {
            return $_GET[$name];
        }
        else if(isset($_POST[$name]))
        {
            return $_POST[$name];
        }
        else if(isset($_COOKIE[$name]))
        {
            return $_COOKIE[$name];
        }
        else if(isset($_SERVER[$name]))
        {
            return $_SERVER[$name];
        }
        else if(isset($_ENV[$name]))
        {
            return $_ENV[$name];
        }
        else
        {
            return $defval;
        }
    }

    /**
     * Get an HTTP header
     */
    public function header($name, $defval=null)
    {
        // SERVER stores headers as HTTP_<UPPERNAME_WITH_UNDERSCORES>
        $tmp = "HTTP_" . str_replace("-", "_", strtoupper($name));

        if(isset($_SERVER[$tmp]))
        {
            return $_SERVER[$tmp];
        }
        else if(isset($_SERVER[$name]))
        {
            return $_SERVER[$name];
        }
        else
        {
            return $defval;
        }
    }

}

