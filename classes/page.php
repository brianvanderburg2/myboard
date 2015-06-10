<?php

/**
 * \file
 * \author      Brian Allen Vanderburg Ii
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\MyBoard;


/**
 * Class represents an output page.
 */
class Page
{
    protected $board = null;
    protected $data = array();

    /**
     * Construct the page
     *
     * \param $board The board instance.
     */
    public function __construct($board)
    {
        $this->board = $board;

        // app is already set for template from the base App class
        $this->data["page"] = $this;
    }

    /**
     * Send a page.
     * \param $template The name of the template to use for this page.
     */
    public function send($template)
    {
        /** \todo Set headers */
        $this->board->getService("template")->send($template, $this->data);
    }

    /**
     * Get data from the page
     *
     * \param $name The name of the item to get;
     * \param $defval The default value if the item is not present.
     * \return The value if present, or the default value if not.
     */
    public function get($name, $defval=null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $defval;
    }

    /**
     * Set data for the page.
     *
     * \param $name The name of the item to set.
     * \param $value The value to set the item to.
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Get all data values.
     *
     * \return The array of data.
     */
    public function getAll()
    {
        return $this->data;
    }

    /* Helpful functions for output
     ******************************/
    public function escHtml($value)
    {
        return htmlspecialchars($value);
    }

    public function escAttr($value)
    {
        return htmlspecialchars($value);
    }
};
