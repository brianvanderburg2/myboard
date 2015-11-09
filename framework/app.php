<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework;

/**
 * This class is a simple base application class, providing a
 * service/dependency injection container and configuration container.
 */
class App
{
    protected $loader = null;

    // currently active app instance
    protected static $instance = null;

    // Configuration for application and services
    protected $config = null;

    // Known services and instances of single-instance (shared) services
    protected $services = array();
    protected $shared = array();

    // Running timer
    protected $timer = null;

    /**
     * Set or get the currently active application instance.
     *
     * \param $active The new value to set the instance to, if specified.
     * \return The previous value of the application instance.
     */
    public static function instance($active=FALSE)
    {
        $current = static::$instance;

        if($active !== FALSE)
        {
            static::$instance = $active;
        }
        return $current;
    }


    /**
     * Construct the base application and set it as the active application.
     *
     * \param $config The configuration values to set.
     */
    public function __construct($config)
    {
        // Start timer and set up active instance
        $this->timer = microtime(TRUE);
        static::instance($this);
   
        // Set up the configuration
        $default_config = array(
        );

        $this->config = array_merge($default_config, $config);

        // Set up shutdown and error handlers
        register_shutdown_function(function() {
            return $this->shutdownHandler();
        });

        if($this->getConfig("app.error.enable_handler", FALSE))
        {
            set_error_handler(function($a, $b, $c, $d, $e) {
                return $this->errorHandler($a, $b, $c, $d, $e);
            });

            set_exception_handler(function($e) {
                return $this->exceptionHandler($e);
            });
        }

        if($this->getConfig("app.error.report_all", TRUE))
        {
            error_reporting(E_ALL);
        }

        if($this->getConfig("app.error.show_user", FALSE))
        {
            ini_set("display_errors", "on");
        }
        else
        {
            ini_set("display_errors", "off");
        }

        // Set up some default services

        // request
        $this->registerService("request", __NAMESPACE__ . "\\Request", array($this));

        // response
        $this->registerService("response", __NAMESPACE__ . "\\Response", array($this));

        // database
        $this->registerService("database", __NAMESPACE__ . "\\Database\\Manager", array($this));

        // template
        $this->registerService("template", __NAMESPACE__ . "\\Template", array($this));

        // mime
        $this->registerService("mime", __NAMESPACE__ . "\\MimeType", array($this));

        // session
        $this->registerService("session", __NAMESPACE__ . "\\Session\\Manager", array($this));
    }

    /**
     * Get the current timer.
     *
     * \return The current timer.
     */
    public function getTimer()
    {
        return microtime(TRUE) - $this->timer;
    }

    /* Service related methods 
     *************************/

    /**
     * Register a service in the container.
     *
     * \param $name The name of the service to be used by getService.
     * \param $cls
     *   - If this is a closure or array, it will be treated as the 
     *     constructor function to call.
     *   - If this is a string, it will be treated as the classname to
     *     create a new instance of.
     * \param $args An array of arguments to pass
     * \return An instance of _AppService.
     */
    public function registerService($name, $cls=null, $args=array())
    {
        $service = new _AppService();

        if($cls)
        {
            if($cls instanceof \Closure or is_array($cls))
            {
                $service->setConstructor($cls, $args);
            }
            else
            {
                $service->setClass($cls, $args);
            }
        }

        $this->services[$name] = $service;
        return $service;
    }

    /**
     * Determine if a service is registered.
     *
     * \param $name The name of the service.
     * \return The result of the test:
     *   - TRUE if the service is registered.
     *   - FALSE if the service is not registered.
     */
    public function hasService($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * Get a service object from the container, creating it and any
     * dependencies if needed.
     *
     * \param $name The name of the service.
     * \return The instance of the service object.
     */
    public function getService($name)
    {
        /* Check shared cache */
        if(isset($this->shared[$name]))
            return $this->shared[$name];

        /* Get service info */
        if(!isset($this->services[$name]))
            return null;

        $service = $this->services[$name];

        /* Create the service */
        $params = array();
        foreach($service->arguments as $arg)
        {
            $params[] = $this->normalizeValue($arg);
        }

        if($service->constructor)
        {
            $cons = $this->normalizeValue($service->constructor);
            if($cons === null)
            {
                throw new Exception("Null constructor for service : {$name}");
            }
            $obj = call_user_func_array($cons, $params);
        }
        else
        {
            $cls = $this->normalizeValue($service->class);
            if($cls === null)
            {
                throw new Exception("Null class for service : {$name}");
            }
            $reflection = new \ReflectionClass($cls);
            $obj = $reflection->newInstanceArgs($params);
        }

        /* Call any methods */
        foreach($service->methods as $method)
        {
            if($method->method instanceof \Closure)
            {
                // Bind closer to allow function to use $this
                // to access public members
                $fn = $method->method->bindTo($obj);
            }
            else if(method_exists($obj, $method->method))
            {
                $fn = array($obj, $method);
            }
            else
            {
                throw new Exception("No such method for service : {$name} : {$method->method}");
            }

            $params = array();
            foreach($method->arguments as $arg)
            {
                $params[] = $this->normalizeValue($arg);
            }

            call_user_func_array($fn, $params);
        }

        /* Save the result if needed */
        if($service->shared)
            $this->shared[$name] = $obj;

        return $obj;
    }

    /**
     * Remove a service from the container.
     *
     * \param $name The name of the service.
     */
    public function removeService($name)
    {
        if(isset($this->services[$name]))
            unset($this->services[$name]);

        if(isset($this->shared[$name]))
            unset($this->shared[$name]);
    }


    /* Configuration related methods 
     *******************************/

    /**
     * Determine if a configuration exists.
     *
     * \param $name The name of the configuration.
     * \return The result of the test:
     *   - TRUE if the configuration exists.
     *   - FALSE if the configuration does not exist.
     */
    public function hasConfig($name)
    {
        return isset($this->config[$name]);
    }

    /**
     * Set the value of a configuration.
     *
     * \param $name The name of the configuration.
     * \param $value The value to set the configuration to.
     */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * Get the normalized value of the configuration.
     *
     * This will perform any subsitutions and replacements as needed.
     *
     * \param $name The name of the configuration.
     * \param $deval The default value if the configuration is not set or can
     *  not be even partialy normalized.  If the value can be partially or entirely
     *  normalized, that value is normalized returned instead.  If the default
     *  value can not be normalized, null is returned.
     * \return The normalized value of the configuration.  This may be a string
     *   if the configuration was a string, otherwise it will be the direct
     *   value of that configuration or its reference.
     */
    public function getConfig($name, $defval=null)
    {
        if(isset($this->config[$name]))
        {
            $value = $this->normalizeValue($this->config[$name]);
            if($value !== null)
            {
                return $value;
            }
        }

        if($defval !== null)
        {
            $defval = $this->normalizeValue($defval);
        }

        return $defval;
    }

    /**
     * Remove a configuration.
     *
     * \param $name The name of the configuration.
     */
    public function removeConfig($name)
    {
        if(isset($this->config[$name]))
            unset($this->config[$name]);
    }

    /**
     * Set all configurations.
     *
     * \param $values An associative array of all configuration names and their values.
     */
    public function setConfigs($values)
    {
        $this->configs = new $values;
    }

    /**
     * Normalize a value based on any references or templates.
     *
     * Normalization is applied to class and constructor names and arguments, method
     * call arguments, and to configurations.  During normalization of an item, if the
     * item can not be partially normalized, null is returned.  If the item being normalized
     * is an array, each key is normalized and the resulting array is returned.  Any key
     * that can not be normalized is set to null. This is performed recursively.  if the
     * item being normalized is a string, substitution is performed.  If any portion of
     * the substition fails, then null is returned instead of the expanded string.
     *
     * \param $value The value to normalize.
     */
    protected function normalizeValue($value)
    {
        if($value instanceof _AppServiceRef)
        {
            return $this->getService($value->name);
        }
        else if($value instanceof _AppConfigRef)
        {
            return $this->getConfig($value->name);
        }
        else if(is_array($value))
        {
            $result = array();
            foreach($value as $key => $value2)
            {
                $result[$key] = $this->normalizeValue($value2);
            }
            return $result;
        }
        else if(is_string($value))
        {
            // If substitution fails anywhere, return null instead of any string.
            $subst_success = TRUE;
            $result = preg_replace_callback(
                "/%(.*?)%/",
                function($matches) use (&$subst_success) {
                    if(strlen($matches[1]))
                    {
                        $subst = $this->getConfig($matches[1]);
                        if($subst === null)
                        {
                            $subst_success = FALSE;
                            return "";
                        }
                        else
                        {
                            return strval($subst);
                        }
                    }
                    else
                    {
                        return "%";
                    }
                },
                $value
            );
            return $subst_success ? $result : null;
        }
        else
        {
            return $value;
        }
    }

    /* Methods to use as a reference in services and configurations.
     **************************************************************/

    /**
     * Create a direct reference to another configuration.
     *
     * \param $name The name of the configuration to refernce.
     * \return The reference object for the configuration.
     */
    public static function ConfigRef($name)
    {
        return new _AppConfigRef($name);
    }

    /**
     * Create a reference to another service.
     *
     * \param $name The name of the service to reference.
     * \return The reference object for the service.
     */
    public static function ServiceRef($name)
    {
        return new _AppServiceRef($name);
    }

    /* Shutdown and error handlers
     *****************************/

    protected function shutdownHandler()
    {
        /** \todo */
    }

    protected function errorHandler($severity, $msg, $file, $line, $context)
    {
        /** \todo */
    }

    protected function exceptionHandler($e)
    {
        /** \todo: do this proper instead of just printing */
        try
        {
            print($e);
        }
        catch(Exception $e)
        {
            print get_class($e) . " thrown inside the exception handler. Message: " . $e->getMessage() . " on line " . $e->getLine();
        }
    }

    /* Dispatch related methods
     **************************/

    /**
     * Execute the application.
     *
     * \param $pathinfo Set to the path info if the default detection doesn't work.
     * \param $extension The extension dispatcher to execute if any.
     */
    public function execute($pathinfo=null, $extension=null)
    {
        $request = $this->getService("request");
        $request->setPathinfo($pathinfo);
        $path = $request->path();

        // Dispatch
        $this->dispatch($request, $path, $extension);

        // Dispatch should exit, not return;
        $this->errorPage($request, 404);

        exit();
    }

    /**
     * Handle dispatching for a particular request after checks have been made.
     */
    protected function dispatch($request, $path, $extension=null)
    {
        if($extension === null)
        {
            $filename = $this->getConfig("app.dispatcher");
        }
        else
        {
            $filename = $this->getConfig("app.dispatcher.{$extension}");
        }

        if($filename !== null)
        {
            $params = ["app" => $this, "request" => $request, "path" => $path];
            $this->loadPhp($filename, $params);
        }
    }

    /**
     * Load a php file using the loader.
     *
     * \param $filename The name of the file to load.
     * \param $params Additional parameters to pass to the loader.
     * \param $override TRUE to override all previous loader parameters.
     * \return The return value of the included the PHP file.
     */
     public function loadPhp($filename, $params=null, $override=FALSE)
     {
        if($this->loader == null)
        {
            $this->loader = new PhpLoader();
        }
        return $this->loader->loadPhp($filename, $params, $override);
     }

    /**
     * Show an error page.
     */
    public function errorPage($request, $code, $msg="")
    {
    }

    /* URL and redirect methods
     **************************/

    /**
     * Get a URL relative to the entry point.
     */
    public function url($url)
    {
        if(is_array($url))
        {
            $url = "/" . implode("/", $url);
        }

        return $this->getService("request")->entry() . $url;
    }

    /**
     * Redirect to a url relative to the entry point.
     */
    public function redirect($url)
    {
        $response = $this->getService("response");
        $response->redirect($this->url($url));
        exit();
    }

    /* Directory handling methods
     ****************************/

    /**
     * Get a data directory.
     * The data directory is determined by the configuration
     * value "app.datadir.<name>"
     *
     * \param $name The name of the data directory to get
     * \param $defval The value to return if the data directory is configured.
     */
    public function getDataDir($name, $defval=null)
    {
        return $this->getConfig("app.datadir.{$name}", $defval);
    }
}

/**
 * This class contains the details of a single application service.
 */
class _AppService
{
    public $class = null;
    public $constructor = null;
    public $arguments = array();
    public $methods = array();
    public $shared = TRUE;

    /**
     * Set the class the service uses.
     *
     * \param $cls The name of the class when creating an instance of this service.
     * \param $args An array of arguments to pass to the class constructor.
     * \return $this
     *
     * \note setClass and setConstructor are mutually exclusive, the last overrides
     *       any previous calls.
     */
    public function setClass($cls, $args=array())
    {
        $this->constructor = null;
        $this->class = $cls;
        $this->arguments = $args;
        return $this;
    }

    /**
     * Set the constructor the service uses.
     *
     * \param $cons The constructor to use to create an instance of this service.
     * \param $args An array of arguments to pass to the constructor function.
     * \return $this
     *
     * \note setClass and setConstructor are mutually exclusive, the last overrides
     *       any previous calls.
     */
    public function setConstructor($cons, $args=array())
    {
        $this->class = null;
        $this->constructor = $cons;
        $this->arguments = $args;
        return $this;
    }

    /**
     * Remove all method calls.
     *
     * \return $this
     */
    public function clearMethodCalls()
    {
        $this->methods = array();
        return $this;
    }

    /**
     * Add a method call.
     *
     * \param $method The name of the method on the service object to call.
     * \param $args An array of arguments to pass to the method call.
     * \return $this
     */
    public function addMethodCall($method, $args=array())
    {
        $this->methods[] = new Generic(array(
            "method" => $method,
            "arguments" => $args
        ));
        return $this;
    }

    /**
     * Set whether the service is shared or not.
     *
     * When a service is shared, only one instance of the service is created and previous
     * calls to getService will use that same instance.  When a service is not shared, each
     * call to getService will create a new instance of that service.  The initial state is
     * shared.
     *
     * \param $shared Whether the service is shared.
     */
    public function setShared($shared=TRUE)
    {
        $this->shared = $shared;
        return $this;
    }
}


/**
 * Base reference
 */
class _AppRef
{
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
}



/**
 * App service reference.
 */
class _AppServiceRef extends _AppRef
{
}

/**
 * App configuration reference.
 */
class _AppConfigRef extends _AppRef
{
}


