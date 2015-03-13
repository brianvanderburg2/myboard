<?php

// File:        security.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Provide some simple security related functions

namespace MyBoard;

class Security
{
    public static function checkPathComponent($part)
    {
        return (bool)preg_match("#^[a-zA-Z0-9][a-zA-Z0-9_\\.-]*$#", $part);
    }

    public static function checkPath($path)
    {
        if(strlen($path) == 0)
            return FALSE;

        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        if(count($parts) == 0)
            return FALSE;

        // Empty first part means it begins with a path separator
        if(strlen($parts[0]) == 0)
            return FALSE;

        while(($part = array_shift($parts)) !== null)
        {
            if(strlen($part) == 0)
            {
                if(count($parts) == 0)
                {
                    // No more parts, means a sep at the end of the path
                    return TRUE;
                }
                else
                {
                    // If more parts, means doubled up on sep
                    return FALSE;
                }
            }
            else if(!static::checkPathComponent($part))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
}

