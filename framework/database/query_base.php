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
abstract class Query_base
{
    public function __construct()
    {
    }

    abstract public function fetch();
    abstract public function close();
    abstract public function fetchAll();

    abstract public function execute($params=null);
    abstract public function rowCount();
    abstract public function lastInsertId();
}

