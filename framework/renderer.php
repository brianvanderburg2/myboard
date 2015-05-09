<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * This class represents a renderer object that can be used to handle a request.
 */

namespace mrbavii\Framework;

/**
 * Base renderer class.
 */
class Renderer
{
    public function render($params)
    {
        if(count($params))
        {
            $name = 'render_' . $params[0];
            if(method_exists($this, $name))
            {
                array_shift($params);
                $this->$name($params);
                return TRUE;
            }
        }

        return FALSE;
    }
}

