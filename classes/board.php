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
    protected $config = null;
    
    // config
    public $userdatadir = null;
    public $userdatamethod = null;

    public $appdatadir = null;
    public $appdatamethod = null;

    public $dbprefix = "";

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
        $this->request = new Request();
    }

    /**
     * lazy setup and access of certain variables.
     */
    public function __get($name)
    {
        switch($name)
        {
            // Template
            case 'template':
                $this->setupTemplate();
                return $this->template;

            // Database
            case 'db':
                $this->setupDatabase();
                return $this->db;

            // Cache
            case 'cache':
                $this->setupCache();
                return $this->cache;

            // Session
            case 'session':
                $this->setupSession();
                return $this->session;

            default:
                Util::triggerGetError($name, debug_backtrace()[0]);
                return null;
        };
    }

    /**
     * Set up the template.
     */
    protected function setupTemplate()
    {
        $this->template = new Template(
            $this->userdatadir . '/templates',
            $this->appdatadir . '/templates',
            array('board' => $this)
        );
    }

    /**
     * Set up the database.
     */
    protected function setupDatabase()
    {
        $config = $this->config;

        $dsn = "mysql:";

        // host/port/socket
        $socket = Util::arrayGet($config, 'database.socket', null, 'unix_socket=');
        if($socket)
        {
            $dsn .= $socket;
        }
        else
        {
            $dsn .= Util::arrayGet($config, 'database.host', 'host=localhost', 'host=');
            $dsn .= Util::arrayGet($config, 'database.port', '', ';port=');
        }

        // database name
        $dsn .= Util::arrayGet($config, 'database.name', '', 'dbname=');

        // username and password
        $user = Util::arrayGet($config, 'database.user')
        $pass = Util::arrayGet($config, 'database.pass')
        $this->config['database.pass'] = "";

        // database table prefix
        $this->dbprefix = Util::arrayGet($config, 'database.prefix', '');

        // connect to db and perform initial setup
        $this->db = new \PDO($dsn, $user, $pass);

        $this->db->exec('SET NAMES utf8');
    }

    /**
     * Set up the cache
     */
    protected function setupCache()
    {
        $this->cache = null;
    }

    /**
     * Set up the session
     */
    protected function setupSession()
    {
        $this->session = null;
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
     * Dispatch the request.
     */
    protected function dispatch()
    {
        if(strlen($this->request->pathinfo) == 0)
        {
            $this->redirect('/index');
        }
        else if(substr($this->request->pathinfo, -1) == '/')
        {
            $this->redirect(rtrim($this->request->pathinfo, '/'));
        }

        // Check each component in the path info
        $found = FALSE;
        $filename = __DIR__ . '/../actions';

        $parts = explode('/', $this->request->pathinfo);
        if(count($parts) == 0)
        {
            $this->redirect('/index');
        }

        if(strlen($parts[0]) == 0) // First part is normally blank
        {
            array_shift($parts);
        }

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
            $params = array(
                'board' => $this,
                'pathinfo' => implode('/', $parts)
            );
            Util::loadPhp($filename, $params);
            return TRUE;
        }
        else
        {
            $this->notfound();
        }
    }

    /**
     * Handle a not-found item.
     */
    public function notfound()
    {
        $response = new Response();
        $response->status(404, 'Not Found');
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
        $response = new Response();
        $response->redirect($this->url($url));
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
        }
        catch(Exception $e)
        {
            print get_class($e) . " thrown inside the exception handler. Message: " . $e->getMessage() . " on line " . $e->getLine();
        }
    }

}

