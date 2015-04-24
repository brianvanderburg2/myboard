<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * This class attempts to implement a basic class for a dependency injection.
 */

namespace MyBoard\Framework;
 

/**
 * The dependency injector class
 * TODO: Document methods and variables
 */
class Injector
{
    protected $_parameters = array();
    protected $_services = array();
    protected $_shared = array();

    public function __construct($parameters=array())
    {
        $this->_parameters = $parameters;
    }

    public function register($name, $cls=null)
    {
        $service = new _InjectorService();

        if($cls)
        {
            if($cls instanceof \Closure)
            {
                $service->setConstructor($cls);
            }
            else
            {
                $service->setClass($cls);
            }
        }

        $this->_services[$name] = $service;
        return $service;
    }

    public function hasParameter($name)
    {
        return isset($this->_parameters[$name]);
    }

    public function setParameter($name, $value)
    {
        $this->_parameters[$name] = $value;
    }

    public function getParameter($name)
    {
        if(!isset($this->_parameters[$name]))
            return null; /** TODO: exception */

        $value = $this->_parameters[$name];
        return $this->_normalizeValue($value);
    }

    public function hasService($name)
    {
        return isset($this->_services[$name]);
    }

    public function getService($name)
    {
        if(!isset($this->_services[$name]))
            return null; /** TODO: exception */

        $service = $this->_services[$name];

        /* Check shared cache */
        if($service->_shared && isset($this->_shared[$name]))
            return $this->_shared[$name];

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
        foreach($service->_methods as $tmp)
        {
            list($method, $args) = $tmp;

            if(!method_exists($obj, $method))
                continue; /** TODO: exception */

            $params = array();
            foreach($args as $arg)
            {
                $params[] = $this->_normalizeValue($arg);
            }

            call_user_func_array(array($obj, $method), $params);
        }

        /* Save the result if needed */
        if($service->_shared)
            $this->_shared[$name] = $obj;

        return $obj;
    }

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
        else
        {
            return $value;
        }
    }

    public static function Parameter($name)
    {
        return new _InjectorParameterRef($name);
    }

    public static function Service($name)
    {
        return new _InjectorServiceRef($name);
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

    public function setClass($cls)
    {
        $this->_class = $cls;
        return $this;
    }

    public function setConstructor($cons)
    {
        $this->_constructor = $cons;
        return $this;
    }

    public function clearArguments($args)
    {
        $this->_arguments = array();
        return $this;
    }

    public function addArgument($arg)
    {
        $this->_arguments[] = $arg;
        return $this;
    }

    public function clearMethodCalls()
    {
        $this->_methods = array();
        return $this;
    }

    public function addMethodCall($method, $args)
    {
        $this->_methods[] = array($method, $args);
        return $this;
    }

    public function setShared($shared)
    {
        $this->_shared = $shared;
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

