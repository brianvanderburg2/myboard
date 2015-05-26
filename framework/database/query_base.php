<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Database;


/**
 * Base database query
 */
class Query_base
{
    public function __construct()
    {
    }

    abstract public function fetch();
    abstract public function close();
    abstract public function fetchAll();
    abstract public function fetchColumn($name);

    abstract public function execute($params=null);
    abstract public function lastInsertId($table=null, $column=null);
}

