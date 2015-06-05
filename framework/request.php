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
    public $pathinfo = null; /**< \brief The PATHINFO of the request */
    public $path = null; /**< \brief The array of path parts. */
    public $method = null; /**< \brief The method of the request.  \see getMethod */
    public $entry = null; /**< \brief The PHP entry point of the request */


    /**
     * Construct the request
     *
     */
    public function __construct()
    {
        // Path info and path
        $this->pathinfo = $this->getPathInfo();

        $this->path = explode("/", $this->pathinfo);
        if(count($this->path) && strlen($this->path[0]) == 0)
        {
            array_shift($this->path); // remove first part since path info starts with "/"
        }

        // sanitize inputs, etc
        $this->method = $this->getMethod();
        $this->entry = $this->getEntryPoint();

    }

    /**
     * Determine the request method
     *
     * \return A string representing the request method:
     *   - head
     *   - get
     *   - post
     *   - unknown
     */
    protected function getMethod()
    {
        switch($_SERVER["REQUEST_METHOD"])
        {
            case "GET":
                $method = "get";
                break;
            case "HEAD":
                $method = "head";
                break;
            case "POST":
                $method = "post";
                break;
            default:
                $method = "unknown";
                break;
        }

        return $method;
    }

    /**
     * Determine the PATH_INFO
     *
     * \param $calc 
     *   - If TRUE, calculate from REQUEST_URI instead of using PATH_INFO
     *   - Otherwise, use PATH_INFO if available else calculate from REQUEST_URI
     */
    protected function getPathInfo($calc=FALSE)
    {
        if($calc || !array_key_exists("PATH_INFO", $_SERVER))
        {
            $pathinfo = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["SCRIPT_NAME"]));
            if(($pos = strpos($pathinfo, "?" . $_SERVER["QUERY_STRING"])) !== FALSE)
            {
                $pathinfo = substr($pathinfo, 0, $pos);
            }

            return $pathinfo;   
        }
        else
        {
            return $_SERVER["PATH_INFO"];
        }
    }

    /**
     * Determine the entry point used by the request.
     */
    protected function getEntryPoint()
    {
        return $_SERVER["SCRIPT_NAME"];
    }
}

