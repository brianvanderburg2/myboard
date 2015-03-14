<?php

// File:        board.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Class representing the board and entry point

namespace MyBoard;

/**
 * Class representing the board state/context
 */
class Board
{
    // Database schema version number
    const DBVERSION = 1;

    // Board version number
    const MAJORVERSION = 0;
    const MINORVERSION = 0;
    const REVISION = 0;

    // Forum variables
    public $config = null;
    protected $_timer = null;

    public $security = null;
    public $request = null;
    public $response = null;
    public $template = null;
    public $db = null;
    public $cache = null;
    public $session = null;
    public $user = null;
    public $installed = FALSE;

    // config
    public $userdatadir = null;
    public $userdatamethod = null;
    public $appdatadir = null;
    public $appdatamethod = null;

    protected $adminkey = null;


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
     * Start the board software
     */
    public function run()
    {
        $this->setup();
        $this->dispatch();
    }

    /**
     * Perform setup
     */
    protected function setup()
    {
        // Start the timer
        $this->_timer = microtime(TRUE);

        // Set up error handling
        register_shutdown_function(function() {
            return $this->shutdownHandler(); 
        });
        set_error_handler(function($a, $b, $c, $d, $e) {
            return $this->errorHandler($a, $b, $c, $d, $e);
        });
        set_exception_handler(function($e) {
            return $this->exceptionHandler($e);
        });

        ini_set('display_errors', 'off');
        error_reporting(E_ALL);

        
        $config = $this->config;

        // user data directory
        $this->userdatadir = Util::arrayGet($config, 'userdata.dir');
        $this->userdatamethod = Util::arrayGet($config, 'userdata.method');

        // app data directory
        $this->appdatadir = __DIR__ . '/../data';
        $this->appdatamethod = Util::arrayGet($config, 'appdata.method');

        // Admin key
        $this->adminkey = Util::arrayGet($config, 'admin.key');

        // Create default objects
        $this->security = new Security($this);
        $this->request = new Request($this);
        $this->response = new Response($this);
        $this->template = new Template($this);
        $this->db = new Database($this);
        $this->cache = new Cache($this);
        $this->session = new Session($this);
        $this->user = new User($this);

        // Get install state
        $this->installed = $this->checkInstall();
    }

    /**
     * Dispatch the request.
     */
    protected function dispatch()
    {
        $pathinfo = $this->request->pathinfo;

        // redirect if needed
        if(strlen($pathinfo) == 0)
        {
            $this->redirect('/index');
        }
        else if(substr($pathinfo, -1) == '/')
        {
            $this->redirect(rtrim($pathinfo, '/'));
        }

        // check the components
        $parts = explode('/', $pathinfo);

        if(strlen($parts[0]) == 0)
            array_shift($parts); // first part is normally empty since pathinfo starts with /

        foreach($parts as $part)
        {
            if(strlen($part) == 0 || !$this->security->checkPathComponent($part))
            {
                $this->notfound();
                exit();
            }
        }

        // determine action and parameters
        $action = (count($parts) > 0) ? $parts[0] : 'index';
        $params = (count($parts) > 1) ? array_slice($parts, 1) : array();

        // redirect to install in not already installed
        if($action != 'install' && $action != 'resources')
        {
            if(!$this->installed)
            {
                $this->redirect('/install');
                exit();
            }
        }

        // execute the action
        $filename = __DIR__ . '/../actions/' . $action . '.php';
        if(is_file($filename))
        {
            Util::loadPhp($filename, array('board' => $this, 'action' => $action, 'params' => $params), TRUE);
        }
        else
        {
            $this->notfound();
        }
        exit();
    }

    /**
     * Calculated values
     */
    public function timer()
    {
        return microtime(TRUE) - $this->_timer;
    }

    /**
     * Check if the database has been installed.
     */
    public function checkInstall()
    {
        return FALSE;
    }

    /**
     * Handle a not-found item.
     */
    public function notfound()
    {
        $this->response->status(404, 'Not Found');
        exit();
    }

    /**
     * Get a URL relative to the entry point
     */
    public function url($url)
    {
        return $this->request->entry . $url;
    }

    /**
     * Redirect relative ot the entry point.
     */
    public function redirect($url)
    {
        $this->response->redirect($this->url($url));
        exit();
    }

    /**
     * Send a file to the browser.
     */
    public function sendfile($file)
    {
        if(!Security::checkPath($file))
        {
            $this->notfound();
        }

        // determine if we are sending user or app file
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
            print($e);
        }
        catch(Exception $e)
        {
            print get_class($e) . " thrown inside the exception handler. Message: " . $e->getMessage() . " on line " . $e->getLine();
        }
    }

}

