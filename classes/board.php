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

    /**
     * Construct the board object
     *
     * \param config The configuration used for the board.
     */
    public function __construct($config)
    {
        // Configuration
        $default_config = array(
            "app.dispatcher.filename" => __DIR__ . "/dispatcher/main.php",
            "app.datadir.app" => __DIR__ . "/../data"
        );

        // Call base constructor with merged configuration
        parent::__construct(array_merge($default_config, $config));
        
        // Register services objects default objects
        $this->registerService("installer", __NAMESPACE__ . "\\Installer", array($this));
        $this->registerService("page", __NAMESPACE__ . "\\Page", array($this))->setShared(FALSE);
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

