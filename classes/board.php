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
class Board
{
    const DBVERSION = 1; /**< \brief Schema version for the database */

    const MAJORVERSION = 0; /**< \brief Major version for board software */
    const MINORVERSION = 0; /**< \brief Minor version for board software */

    // Forum variables
    public $config = null;
    protected $_timer = null;

    public $services = null;
    public $request = null;
    public $response = null;
    public $template = null;
    public $db = null;
    public $cache = null;
    public $session = null;
    public $user = null;

    // config
    public $userdata = null;
    public $appdata = null;

    protected $adminkey = null;


    /**
     * Construct the board object
     *
     * \param config The configuration used for the board.
     */
    public function __construct($config)
    {
        $this->config = array_merge($this->defaultConfig(), $config);
        $this->setup();
    }

    /**
     * Default board configuration
     */
    protected function defaultConfig()
    {
        return array();
    }

    /**
     * Start the board software
     */
    public function execute()
    {
        $this->dispatch();
    }

    /**
     * Perform setup.
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
        $this->userdata = new Framework\Attr(array(
            'dir' => Util::arrayGet($config, 'userdata.dir'),
            'callback' => Util::arrayGet($config, 'userdata.callback')
        ));

        // app data directory
        $this->appdata = new Framework\Attr(array(
            'dir' => __DIR__ . '/../data',
            'callback' => Util::arrayGet($config, 'appdata.callback')
        ));

        // Admin key
        $this->adminkey = Util::arrayGet($config, 'admin.key');

        // Register injector objects default objects
        $services = new Framework\Injector($this->config);
        $this->services = $services;

        $services->register('request', 'mrbavii\\Framework\\Request');
        $services->register('response', 'mrbavii\\Framework\\Response', array(
            Framework\Injector::Service('request')
        ));
        $services->register('template', 'mrbavii\\Framework\\Template', array(
            array($this->userdata->dir . '/templates', $this->appdata->dir . '/templates'),
            array('board' => $this),
            '.php'
        ));

        $this->db = new Database($this);
        $this->installer = new Installer($this);

        $services->register('cache', 'mrbavii\\Framework\\Cache');
        $services->register('captcha', 'mrbavii\\Framework\\Captcha');
        $services->register('session', 'mrbavii\\Framework\\Session');
        
        $this->user = new User($this);
    }

    /**
     * Dispatch the request.
     */
    protected function dispatch()
    {
        $pathinfo = $this->services->request->pathinfo;

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
            if(!$this->installer->installed)
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
     * Handle a not-found item.
     */
    public function notfound()
    {
        $this->services->response->status(404, 'Not Found');
        exit();
    }

    /**
     * Get a URL relative to the entry point
     */
    public function url($url)
    {
        return $this->services->request->entry . $url;
    }

    /**
     * Redirect relative ot the entry point.
     */
    public function redirect($url)
    {
        $this->services->response->redirect($this->url($url));
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

