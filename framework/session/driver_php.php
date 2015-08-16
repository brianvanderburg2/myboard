<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Session;
use mrbavii\Framework;


/**
 * PHP driver that uses the PHP session handling
 */
class Driver_php extends Driver
{
    protected $started = FALSE;
    protected $prefix = "";

    public function __construct($config)
    {
        parent::__construct($config);
        if(isset($config["prefix"]))
        {
            $this->prefix = $config["prefix"];
        }
    }

    protected function start()
    {
        if(!$this->started)
        {
            // close any possibly auto-started session
            session_write_close();

            // start/resume a session
            session_start();
            $this->started = TRUE;
        }
    }

    public function close()
    {
        session_write_close();
    }

    public function destroy()
    {
        // This code is borrowed from PHP online docs
        $_SESSION = array();
        
        if(ini_get('session.use_cookies'))
        {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }

    public function set($name, $value)
    {
        $this->start();
        $_SESSION[$this->prefix . $name] = $value;
    }

    public function get($name, $defval=null)
    {
        $this->start();
        if(isset($_SESSION[$this->prefix . $name]))
        {
            return $_SESSION[$this->prefix . $name];
        }
        else
        {
            return $defval;
        }
    }

    public function delete($name)
    {
        $this->start();
        unset($_SESSION[$this->prefix . $name]);
    }

    public function has($name)
    {
        $this->start();
        return isset($_SESSION[$this->prefix . $name]);
    }

}

