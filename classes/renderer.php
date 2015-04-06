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

    /**
     * This is a map of regular expressions to either a function
     * or class name.  If the default render method is not overriden
     * in a base class, this is searched.  If the regular expression
     * matches, then either the function is called on the current object
     * if the value of the entry begins with ':', or the class of the value
     * is constructed and it's render method is called.  In either case
     * the remaining parameters are stil passed in.
     */
    protected static $renderers = array();

    public function __construct($board)
    {
        $this->board = $board;
    }

    public function render($params)
    {
        foreach(static::$renderers as $re => $name)
        {
            if(preg_match($re, $params[0]))
            {
                array_shift($params);

                if(substr($name, 0, 1) == ':')
                {
                    $name = substr($name, 1);
                    $this->$name($params);
                }
                else
                {
                    $renderer = new $name($this->board);
                    $renderer->render($params);
                }
                return;
            }
        }

        $this->board->notfound();
    }
}

