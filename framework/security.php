<?php

// File:        security.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Provide some simple security related functions

namespace mrbavii\Framework;

class Security
{
    public static function checkPathComponent($part)
    {
        return (bool)preg_match("#^[a-zA-Z0-9][a-zA-Z0-9_\\.-]*$#", $part);
    }

    public static function checkPath($path, $allowdir=FALSE)
    {
        // Make it an array if not already
        if(is_array($path))
        {
            $parts = $path;
        }
        else
        {
            if(strlen($path) == 0)
                return FALSE;

            $parts = explode("/", $path);
        }

        if(count($parts) == 0)
            return FALSE;

        // Empty first part means it begins with a path separator
        if(strlen($parts[0]) == 0)
            return FALSE;

        // Check each part
        $len = count($parts);
        for($i = 0; $i < $len; $i++)
        {
            $part = $parts[$i];
            if(strlen($part) == 0)
            {
                if($i + 1 == $len)
                {
                    // No more parts, means a seperator at the end of the path
                    return $allowdir;
                }
                else
                {
                    // If more parts, means doubled up on seperator
                    return FALSE;
                }
            }
            else if(!self::checkPathComponent($part))
            {
                return FALSE;
            }
        }

        return TRUE;
    }
}

