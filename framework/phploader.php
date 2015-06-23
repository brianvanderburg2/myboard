<?php

// File:        php.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple PHP loader classs

namespace mrbavii\Framework;

/**
 * A PHP loader loads PHP files while extracting parameters.  Nested calls to the
 * load method will merge parameters with previous calls.
 */
trait PhpLoader
{
    protected $loadPhpParams = array();

    public function loadPhp($filename, $params=null, $override=FALSE)
    {
        $saved = null;
        if($params !== null)
        {
            $saved = $this->loadPhpParams;
            if($override)
            {
                $this->loadPhpParams = $params;
            }
            else
            {
                $this->loadPhpParams = array_merge($this->loadPhpParams, $params);
            }
        }

        try
        {
            $result = Util::loadPhp($filename, $this->loadPhpParams, TRUE);
            if($saved !== null)
            {
                $this->loadPhpParams = $saved;
            }
            return $result;
        }
        catch(\Exception $e)
        {
            if($saved !== null)
            {
                $this->loadPhpParams = $saved;
            }
            throw $e;
        }
    }
}

