<?php

// File:        util.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Some utility functions

namespace mrbavii\Framework;

class Util
{
    public static function loadPhp($__filename__, $__params__=array(), $__require__=TRUE)
    {
        extract($__params__);
        if($__require__)
        {
            return require $__filename__;
        }
        else
        {
            return include $__filename__;
        }
    }

    public static function loadIni($filename)
    {
        return parse_ini_file($filename, TRUE);
    }

    public static function guid($namespace='')
    {
        // TODO: use uuid_create also if available
        if(function_exists('com_create_guid'))
        {
            return trim(com_create_guid(), '{}');
        }
        else
        {
            return static::internalGuid($namespace);
        }
    }

    public static function internalGuid($namespace='')
    {
        // This code comes from http://php.net/manual/en/function.uniqid.php
        // Curly brackets are not included
        static $guid = '';

        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['LOCAL_ADDR'];
        $data .= $_SERVER['LOCAL_PORT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];

        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash,  0,  8) .
            '-' .
            substr($hash,  8,  4) .
            '-' .
            substr($hash, 12,  4) .
            '-' .
            substr($hash, 16,  4) .
            '-' .
            substr($hash, 20, 12);

        return $guid;
    }

    public static function startsWith($str, $needle, $case=FALSE)
    {
        $len = strlen($needle);
        if($len == 0)
            return TRUE;

        if($case)
        {
            return strncasecmp($str, $needle, $len) == 0;
        }
        else
        {
            return strncmp($str, $needle, $len) == 0;
        }
    }

    public static function endsWith($str, $needle, $case=FALSE)
    {
        $len = strlen($needle);
        if($len == 0)
            return TRUE;

        if($case)
        {
            return strcasecmp(substr($str, -$len), $needle) == 0;
        }
        else
        {
            return substr($str, -$len) == $needle;
        }
    }

    public static function arrayGet($arr, $key, $default=null, $prepend="", $append="")
    {
        return isset($arr[$key]) ? "{$prepend}{$arr[$key]}{$append}" : $default;
    }
}

