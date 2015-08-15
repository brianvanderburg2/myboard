<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework\Database;

/**
 * Base driver object 
 */
abstract class Driver
{
    use \mrbavii\Framework\Traits\Attr;

    protected $config;

    /**
     * Construct the database driver
     *
     * \param $config The database configuration.
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get the configured table prefix.
     *
     * \return The preset set from the database configuration.
     */
    public function getPrefix()
    {
        return isset($this->config["prefix"]) ? $this->config["prefix"] : "";
    }

    /**
     * Get the tables of the database.
     *
     * \return An array of all tables in the database.
     */
    abstract public function getTables();

    /**
     * Determine if a table exists.
     *
     * \param name The name of a table to test for
     * \return TRUE if the table exists, otherwise false.
     */
    public function tableExists($name)
    {
        return in_array($name, $this->getTables());
    }

    /**
     * Get the columns of a table.
     *
     * \param $table The name of the table.
     * \return An array of column names in the table.
     */
    abstract public function getColumns($table);

    /**
     * Determine if the database is in a transaction.
     *
     * \return TRUE if a transaction is in progress, otherwise FALSE
     */
    abstract public function inTransaction();

    /**
     * Begin a transaction.
     */
    abstract public function begin();

    /**
     * Commit the transaction.
     */
    abstract public function commit();

    /**
     * Roll back the transaction.
     */
    abstract public function rollback();

    /**
     * Execute a statement
     *
     * \param $statement The statement to execute.
     * \param $params The parameters to use in the statement.
     * \return The number of affected rows.
     */
    abstract public function execute($statement, $params=null);

    /**
     * Execute a query
     *
     * \param $statement The statement to execute.
     * \param $params The parameters to use in the statement.
     * \return A query result as derived from Query_base
     */
    abstract public function query($statement, $params=null);

    /**
     * Compile a prepared query
     *
     * \param $statement The statement of the query.
     * \return A query object derived from Query_base.
     */
    abstract public function prepare($statement);

    /**
     * Determine the last row id of the last inserted object
     *
     * \param $seq - The sequence to use in getting the id.
     * \return The id of the last inserted row.
     */
    abstract public function lastInsertId($seq=null);

    /**
     * Quote a value.
     *
     * \param $string The value to quote
     * \return The value quoted for the database.
     */
    abstract public function quote($string);
}

