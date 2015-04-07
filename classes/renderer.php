<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * This class represents a base renderer object that is constructed to handle
 * a request.
 */

namespace MyBoard;

/**
 * Base renderer class.
 */
class Renderer
{
    protected $board = null;

    public function __construct($board)
    {
        $this->board = $board;
    }

    public function render($params)
    {
        if(count($params))
        {
            $name = 'render_' . $params[0];
            if(method_exists($this, $name))
            {
                array_shift($params);
                $this->$name($params);
                return;
            }
        }

        $this->board->notfound();
    }
}

