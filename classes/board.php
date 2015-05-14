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
    const DBVERSION = 1; /**< \brief Schema version for the database */

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
            'app.dispatcher.class' => __NAMESPACE__ . '\\Dispatcher\\Main'
        );

        // Call base constructor with merged configuration
        parent::__construct(array_merge($default_config, $config));
        
        // Register services objects default objects
        $this->registerService('installer', __NAMESPACE__ . '\\Installer', array($this));
    }

    public function errorPage($request, $code, $msg='')
    {
        if($code == 404)
        {
            $this->getService('response')->status(404, 'Not Found');
        }
    }
}

