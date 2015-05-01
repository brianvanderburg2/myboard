<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 */

namespace MyBoard\Framework;
 

/**
 * This class implements a basic dependency injection system.
 *
 * The parameters in the container can be reference by other parameters and
 * in arguments in two ways:
 *
 *  - Direct reference by using Injector::Parameter("name").  When referenced
 *    like this, the value of the parameter specified by "name" will be used
 *    directly.
 *
 *    \code{.php}
 *    array("name1" => 100, "name2" => Injector::Parameter("name1"))
 *    \endcode
 *
 *    or
 *
 *    \code{.php}
 *    $injector->register("service"", "Constructor", array(Injector::Parameter("name1")))
 *    \endcode
 *
 *  - Indirect reference by using a template pattern in the form of "%<name>%".
 *    If name is omitted, the template substitutes a single "%" in the place.
 *    When used like this, the parameter is converted to a string and substituted
 *    in the result:
 *
 *    \code{.php}
 *    array("name1" => 100, "name2" => "value: %name1%")
 *    \endcode
 *
 *    or
 *
 *    \code{.php}
 *    $injector->register("service", "Constructor", array("value: %name1%"))
 *    \endcode
 *
 *  The services in a container can be referenced in arguments by using the
 *  Injector::Service("name") method:
 *
 *  \code{.php}
 *  $injector->register("service1", "MyServiceClass");
 *  $injector->register("service2", "MyService2Class", array(Injector::Service("service1")));
 *  \endcode
 */
class Injector implements \ArrayAccess
{
    protected $_parameters = array();
    protected $_services = array();
    protected $_shared = array();

    /**
     * Construct the injector container.
     *
     * \param $parameters An array of initial parameters for the container.
     */
    public function __construct($parameters=array())
    {
        $this->_parameters = $parameters;
    }

    /**
     * Register a service in the container.
     *
     * \param $name The name of the service to be used by getService.
     * \param $cls
     *   - If this is a closure, it will be treated as the constructor
     *     function to call.
     *   - If this is a string, it will be treated as the classname to
     *     create a new instance of.
     * \param $args An array of arguments to pass
     * \return An instance of _InjectorService.
     */
    public function register($name, $cls=null, $args=array())
    {
        $service = new _InjectorService();

        if($cls)
        {
            if($cls instanceof \Closure)
            {
                $service->setConstructor($cls, $args);
            }
            else
            {
                $service->setClass($cls, $args);
            }
        }

        $this->_services[$name] = $service;
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
        return isset($this->_services[$name]);
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
        if(isset($this->_shared[$name]))
            return $this->_shared[$name];

        /* Get service info */
        if(!isset($this->_services[$name]))
            return null; /** TODO: exception */

        $service = $this->_services[$name];

        /* Create the service */
        $params = array();
        foreach($service->_arguments as $arg)
        {
            $params[] = $this->_normalizeValue($arg);
        }

        if($service->_constructor)
        {
            $obj = call_user_func_array($service->constructor, $params);
        }
        else
        {
            $reflection = new \ReflectionClass($service->_class);
            $obj = $reflection->newInstanceArgs($params);
        }

        /* Call any methods */
        foreach($service->_methods as $method)
        {
            if(!method_exists($obj, $method->_method))
                continue; /** TODO: exception */

            $params = array();
            foreach($method->_arguments as $arg)
            {
                $params[] = $this->_normalizeValue($arg);
            }

            call_user_func_array(array($obj, $method->_method), $params);
        }

        /* Save the result if needed */
        if($service->_shared)
            $this->_shared[$name] = $obj;

        return $obj;
    }

    /**
     * Remove a service from the container.
     *
     * \param $name The name of the service.
     */
    public function removeService($name)
    {
        if(isset($this->_services[$name]))
            unset($this->_services[$name]);

        if(isset($this->_shared[$name]))
            unset($this->_shared[$name]);
    }

    /**
     * Determine if a parameter exists.
     *
     * \param $name The name of the parameter.
     * \return The result of the test:
     *   - TRUE if the parameter exists.
     *   - FALSE if the parameter does not exist.
     */
    public function hasParameter($name)
    {
        return isset($this->_parameters[$name]);
    }

    /**
     * Set the value of a parameter.
     *
     * \param $name The name of the parameter.
     * \param $value The value to set the parameter to.
     */
    public function setParameter($name, $value)
    {
        $this->_parameters[$name] = $value;
    }

    /**
     * Get the normalized value of the parameter.
     *
     * This will perform any subsitutions and replacements as needed.
     *
     * \param $name The name of the parameter.
     * \return The normalized value of the parameter.  This may be a string
     *   if the parameter was a string, otherwise it will be the direct
     *   value of that parameter or it's reference.
     */
    public function getParameter($name)
    {
        if(!isset($this->_parameters[$name]))
            return null; /** TODO: exception */

        $value = $this->_parameters[$name];
        return $this->_normalizeValue($value);
    }

    /**
     * Remove a parameter.
     *
     * \param $name The name of the parameter.
     */
    public function removeParameter($name)
    {
        if(isset($this->_parameters[$name]))
            unset($this->_parameters[$name]);
    }

    /**
     * Set all parameters.
     *
     * \param $values An associative array of all parameter names and their values.
     */
    public function setParameters($values)
    {
        $this->_parameters = $values;
    }

    /**
     * Normalize a value based on any references or templates.
     *
     * \param $value The value to normalize.
     */
    protected function _normalizeValue($value)
    {
        if($value instanceof _InjectorServiceRef)
        {
            return $this->getService($value->name);
        }
        else if($value instanceof _InjectorParameterRef)
        {
            return $this->getParameter($value->name);
        }
        else if(is_array($value))
        {
            $result = array();
            foreach($value as $key => $value2)
            {
                $result[$key] = $this->_normalizeValue($value2);
            }
            return $result;
        }
        else if(is_string($value))
        {
            return preg_replace_callback(
                '/%(.*?)%/',
                function($matches){
                    return strlen($matches[1]) ? strval($this->getParameter($matches[1])) : '%';
                },
                $value
            );
        }
        else
        {
            return $value;
        }
    }

    /* Methods for use in service declarations */

    /**
     * Create a direct reference to another parameter.
     *
     * \param $name The name of the parameter to refernce.
     * \return The reference object for the parameter.
     */
    public static function Parameter($name)
    {
        return new _InjectorParameterRef($name);
    }


    /**
     * Create a reference to another service.
     *
     * \param $name The name of the service to reference.
     * \return The reference object for the service.
     */
    public static function Service($name)
    {
        return new _InjectorServiceRef($name);
    }

    /* Methods for ArrayAccess */

    /**
     * Test if a parameter exists.
     */
    public function offsetExists($offset)
    {
        return $this->hasParameter($offset);
    }

    /**
     * Get a parameter.
     */
    public function offsetGet($offset)
    {
        return $this->getParameter($offset);
    }

    /**
     * Set a parameter.
     */
    public function offsetSet($offset, $value)
    {
        $this->setParameter($offset, $value);
    }

    /**
     * Remove a parameter.
     */
    public function offsetUnset($offset)
    {
        $this->removeParameter($offset);
    }

    /* Methods for direct access */

    /**
     * Test if a service exists.
     */
    public function __isset($name)
    {
        return $this->hasService($name);
    }

    /**
     * Get a service.
     */
    public function __get($name)
    {
        return $this->getService($name);
    }

    /**
     * Remove a service.
     */
    public function __unset($name)
    {
        $this->removeService($name);
    }
}

/**
 * This class contains the details of a single injector service.
 */
class _InjectorService
{
    public $_class = null;
    public $_constructor = null;
    public $_arguments = array();
    public $_methods = array();
    public $_shared = TRUE;

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
        $this->_constructor = null;
        $this->_class = $cls;
        $this->_arguments = $args;
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
        $this->_class = null;
        $this->_constructor = $cons;
        $this->_arguments = $args;
        return $this;
    }

    /**
     * Remove all method calls.
     *
     * \return $this
     */
    public function clearMethodCalls()
    {
        $this->_methods = array();
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
        $this->_methods[] = new Attr(array(
            '_method' => $method,
            '_arguments' => $args
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
        $this->_shared = $shared;
        return $this;
    }
}


/**
 * Injector reference
 */
class _InjectorRef
{
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
}



/**
 * Injector service reference.
 */
class _InjectorServiceRef extends _InjectorRef
{
}

/**
 * Injector parameter reference.
 */
class _InjectorParameterRef extends _InjectorRef
{
}

