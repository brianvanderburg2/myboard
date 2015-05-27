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
abstract class Query
{
    /**
     * Construct the query.
     */
    public function __construct()
    {
    }

    /**
     * Destruct the query.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Retrieve a row from the query.
     *
     * \return An array containing both named and index columns, FALSE if no data.
     */
    abstract public function fetch();

    /**
     * Retrieve all rows from the query.
     *
     * \return An array containing each row of data
     */
    abstract public function fetchAll();

    /**
     * Close the cursor.  This should be called when finished with
     * the query and before calling execute again.
     */
    abstract public function close();

    /**
     * Execute a prepared query.
     *
     * \param $params Parameters to pass to the query.
     */
    abstract public function execute($params=null);

    /**
     * Determine the number of affected rows by the last
     * execution.  This should only be used with INSERT/UPDATE/DELETE
     *
     * \return The number of affected rows.
     */
    abstract public function rowCount();

    /**
     * Determine the last insert id
     *
     * \param $seq The sequence to use in getting the id.
     * \return The id of the last inserted row.
     */
    abstract public function lastInsertId($seq=null);
}

