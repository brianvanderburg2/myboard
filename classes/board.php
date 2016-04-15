<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * This class represents the board and serves as the entry point.  It
 * contains other objects and dispatches the request to the correct
 * action file.
 */

namespace mrbavii\MyBoard;
use mrbavii\Framework;

/**
 * Class representing the board state/context
 */
class Board extends Framework\App
{
    const MAJORVERSION = 0; /**< \brief Major version for board software */
    const MINORVERSION = 0; /**< \brief Minor version for board software */

    protected $_baseurl = null;
    protected $_basedir = null;

    /**
     * Construct the board object
     */
    public function __construct()
    {
        // Configuration
        $required_config = array(
            "app.dispatcher" => __DIR__ . "/../dispatch/main.php",
            "app.datadir.app" => __DIR__ . "/../data"
        );
        $default_config = array(
        );
        $user_config = $this->loadPhp($this->userPath("/config.php"));

        // Call base constructor with merged configuration
        parent::__construct(array_merge(
            $default_config, $user_config, $required_config
        ));
        
        // Register services objects default objects
        $this->registerService("util", __NAMESPACE__ . "\\Util");
    }

    /**
     * Get a URL relative to the entry point.
     */
    public function url($url="")
    {
        return $this->getService("request")->entry() . $url;
    }

    /**
     * Redirect to a url relative to the entry point.
     */
    public function redirect($url="")
    {
        $response = $this->getService("response");
        $response->redirect($this->url($url));
        exit();
    }

    /**
     * Get a URL relative to the base contents.
     */
    public function topUrl($url="")
    {
        $entry = $this->url();
        $last = strrpos($entry, "/");
        return substr($entry, 0, $last) . $url;
    }

    public function topPath($path="")
    {
        $top = dirname(__DIR__);
        return $top . $path;
    }

    public function userUrl($url="")
    {
        return $this->topUrl("/userdata" . $url);
    }

    public function userPath($path="")
    {
        return $this->topPath("/userdata" . $path);
    }

    public function dataUrl($url="")
    {
        return $this->topUrl("/data" . $url);
    }

    public function dataPath($path)
    {
        return $this->topPath("/data" . $path);
    }

    public function errorPage($request, $code, $msg="")
    {
        if($code == 404)
        {
            $this->getService("response")->status(404, "Not Found");
        }
    }

    public function checkAdminKey($password)
    {
        /** \todo: create a framework Password helper. */

        $key = $this->getConfig("admin.key");
        if($key === null)
            return FALSE;

        return password_verify($password, $key);
    }

    public function createAdminKey($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}

