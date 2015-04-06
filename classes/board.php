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

namespace MyBoard;

/**
 * Class representing the board state/context
 */
class Board
{
    const DBVERSION = 1; /**< \brief Schema version for the database */

    const MAJORVERSION = 0; /**< \brief Major version for board software */
    const MINORVERSION = 0; /**< \brief Minor version for board software */

    // Forum variables
    public $config = null;
    protected $_timer = null;

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
    public function execute()
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
        $this->userdatasendfile = Util::arrayGet($config, 'userdata.sendfile');

        // app data directory
        $this->appdatadir = __DIR__ . '/../data';
        $this->appdatasendfile = Util::arrayGet($config, 'appdata.sendfile');

        // Admin key
        $this->adminkey = Util::arrayGet($config, 'admin.key');

        // Create default objects
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
        $path = explode('/', $pathinfo);

        if(strlen($path[0]) == 0)
            array_shift($path); // first part is normally empty since pathinfo starts with /

        foreach($path as $part)
        {
            if(strlen($part) == 0 || !Security::checkPathComponent($part))
            {
                $this->notfound();
                exit();
            }
        }

        // redirect to install in not already installed
        if($path[0] != 'install' && $path[0] != 'resource')
        {
            if(!$this->installed)
            {
                $this->redirect('/install');
                exit();
            }
        }

        // Otherwise, render the result
        $renderer = new Renderer\Dispatch($this);
        $renderer->render($path);
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

