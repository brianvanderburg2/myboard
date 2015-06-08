<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Traits;

/**
 * A basic attributes container trait.
 *
 * The idea behind attributes is that a connection to a specific
 * object (database, cache, etc) can expose certain attributes.
 * Some of these attributes may represent capabilities of the
 * connection.  Others may help abstract specific data for
 * the connection.
 */
trait Attr
{
    protected $attr = array();

    /**
     * Determine if the object has a certain attribute.
     *
     * \param $name The attribute name to query
     * \return TRUE if the attribute exists, otherwise FALSE
     */
    public function hasAttribute($name)
    {
        return isset($this->attr[$name]);
    }

    /**
     * Get the value of an attribute
     *
     * \param $name The attribute name to query
     * \param $def The default value
     * \return The attribute value if it exists, otherwise the default value.
     */
    public function getAttribute($name, $def=null)
    {
        return isset($this->attr[$name]) ? $this->attr[$name] : $def;
    }

}

