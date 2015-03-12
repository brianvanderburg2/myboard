<?php

// File:        board.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Class representing the board and entry point

namespace MyBoard;
use MyBoard\Helper;


/**
 * Class representing the board state/context
 */
class Board
{
    protected $config = null;
    
    // config
    protected $userdatadir = null;
    protected $userdatamethod = null;
    protected $userdataurl = null;

    protected $appdatadir = null;
    protected $appdatamethod = null;
    protected $appdataurl = null;

    protected $adminkey = null;

    protected $dbhost = null;
    protected $dbname = null;
    protected $dbprefix = null;
    protected $dbuser = null;
    protected $dbpass = null;


    /**
     * Construct the board object
     *
     * \param config The configuration used for the board.
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Perform setup
     */
    protected function setup()
    {
        // Set up error handling
        register_shutdown_function(array($this, 'shutdownHandler'));
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
        ini_set('display_errors', 'off');
        error_reporting(E_ALL);

        // Parse config and setup board variables
        

        // Create default objects
        $this->request = new Helper\Request();
    }

    /**
     * lazy setup of certain variables.
     */
    public function __get($name)
    {
        switch($name)
        {
            case 'template':
                $this->template = new Helper\Template(
                    $this->userdatadir . '/templates',
                    $this->appdatadir . '/templates',
                    array('board' => $this)
                );
                return $this->template;
                break;

            default:
                Helper\Util::triggerGetError($name, debug_backtrace()[0]);
                return null;
        };
    }

    /**
     * Start the board software
     */
    public function run()
    {
        $this->setup();
        $this->request->dispatch(__DIR__ . '/../actions', array('board' => $this));
    }

    /**
     * Our custom shutdown handler
     */
    protected function shutdownHandler()
    {
    }

    /**
     * Our custom error handler
     */
    protected function errorHandler($severity, $msg, $file, $line, $context)
    {
    }

    /**
     * Our custom exception handler
     */
    protected function exceptionHandler($e)
    {
        try
        {
        }
        catch(Exception $e)
        {
            print get_class($e) . " thrown inside the exception handler. Message: " . $e->getMessage() . " on line " . $e->getLine();
        }
    }
}

