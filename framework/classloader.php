<?php

// File:        ClassLoader.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Load files containing classes on demand.

namespace mrbavii\Framework;

class ClassLoader
{
    protected static $loaders = array();
    protected static $installed = FALSE;

    public static function register($ns, $dir, $ext=null)
    {
        static::install();

        // Ensure namespace ends with "\", remove any doubles
        $ns = preg_replace("/\\\\+/", "\\", trim($ns, "\\")) . "\\";
        
        $loader = new _ClassLoaderEntry($ns, $dir, $ext);
        static::$loaders[] = $loader;
        return $loader;
    }

    public static function install()
    {
        if(!static::$installed)
        {
            static::$installed = TRUE;
            spl_autoload_register(array(__CLASS__, "loadClass"));
        }
    }

    public static function loadClass($classname)
    {
        foreach(static::$loaders as $loader)
        {
            if($loader->loadClass($classname))
            {
                break;
            }
        }
    }
}

class _ClassLoaderEntry
{
    protected $ns = null;
    protected $dir = null;
    protected $ext = ".php";
    protected $required = FALSE;

    public function __construct($ns, $dir, $ext=null)
    {
        $this->ns = $ns;
        $this->dir = $dir;

        if($ext !== null)
        {
            $this->ext = $ext;
        }
    }

    public function setExtension($ext)
    {
        $this->ext = $ext;
        return $this;
    }

    public function setRequired($required=TRUE)
    {
        $this->required = $required;
        return $this;
    }

    public function loadClass($classname)
    {
        // Check we are loading only for the desired namespace
        $len = strlen($this->ns);
        if(strlen($classname) <= $len || substr_compare($classname, $this->ns, 0, $len) != 0)
            return FALSE;

        // Remove the registered namespace portion
        $classname = substr($classname, $len);

        // Determine the filename portion. Do not replace "_"
        $filename = $this->dir . DIRECTORY_SEPARATOR .
                    strtolower(str_replace("\\", DIRECTORY_SEPARATOR, $classname)) .
                    $this->ext;

        // Load the file and indicate that we handled it
        static::loadFile($filename, $this->required);
        return TRUE;
    }

    protected static function loadFile($__filename__, $__required__)
    {
        if($__required__)
        {
            require $__filename__;
        }
        else
        {
            include $__filename__;
        }
    }
}

