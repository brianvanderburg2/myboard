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
class Driver_base
{
    protected $app;
    protected $config;
    protected $attr;

    /**
     * Construct the database driver
     *
     * \param $app The application instance.
     * \param $config The database configuration.
     */
    public function __construct($app, $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->attr = array();
    }

    /**
     * Determine if the driver has a certain attribute.
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

    /**
     * Get the configured table prefix.
     *
     * \return The preset set from the database configuration.
     */
    public function getPrefix()
    {
        return isset($this->config['prefix']) ? $this->config['prefix'] : '';
    }

    // Table information

    /**
     * Get the tables of the database.
     *
     * \return An array of all tables in the database.
     */
    abstract public function getTables();

    /**
     * Get the columns of a table.
     *
     * \param $table The name of the table.
     * \return An array of column names in the table.
     */
    abstract public function getColumns($table);

    /**
     * Get the create statement for a table.
     *
     * \param $table The name of the table.
     * \return A string representing the create statement for a table.
     */
    abstract public function getCreate($table);

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
     * \param $table - If needed, this specifies the table of the last inserted row id
     * \param $column - If needed, this specifies the column that contains the row id
     *
     * \note The parameters are mainly for pgsql, which will normally create a sequence
     *  specially for the serial column of the table and will, in those cases, only
     *  work when the serial column is not specified during the insert.  Of course, if
     *  the serial column is specified, then there is no need to use this function as the
     *  value of that column was known during the insert.
     */
    abstract public function lastInsertId($table=null, $column=null);

    /**
     * Quote a value
     *
     * \param $string The value to quote
     * \return The value quoted for the database.
     */
    abstract public function quote($string);
}

