<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework\Database;

/**
 * MySQL driver
 */
class Driver_mysql extends Driver_pdo
{
    // Constructor
    public function __construct($config)
    {
        // Prepare the DSN
        $dsn = 'mysql:';
        if(isset($config['socket']))
        {
            $dsn .= "unix_socket={$config['socket']}";
        }
        else
        {
            $dsn .= isset($config['host']) ? "host={$config['host']}" : "host=localhost";
            $dsn .= isset($config['port']) ? ";port={$config['port']}" : '';
        }

        $dsn .= isset($config['dbname']) ? ";dbname={$config['dbname']}" : '';
        $dsn .= ";charset=utf8";

        // Establish connection
        $config['dsn'] = $dsn;
        parent::__construct($config);

        try
        {
            // Enable foreign key constraints
            $this->pdo->exec('SET foreign_key_checks = 1');
            // SERIALIZABLE isolation level
            $this->pdo->exec('SET TRANSACTION ISOLATION_LEVEL SERIALIZABLE');
            // Set UTF-8
            $this->pdo->exec("SET NAMES 'utf8'");
            $this->pdo->exec("SET CHARACTER SET utf8");
            $this->pdo->exec("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    // Get the list of tables
    public function getTables()
    {
        $results = array();

        $query = $this->query("SHOW TABLES");
        while(($result = $query->fetch()) !== FALSE)
        {
            $results[] = $result[0];
        }

        return $results;
    }

    // Get the list of columns for a table
    public function getColumns($table)
    {
        $results = array();

        $query = $this->query("SHOW COLUMNS FROM `$table`");
        while(($result = $query->fetch()) !== FALSE)
        {
            $results[] = $result[0];
        }

        return $results;
    }
}

