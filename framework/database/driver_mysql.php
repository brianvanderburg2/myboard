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

    public function __construct($app, $config)
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
        parent::__construct($app, $config);

        // Enable foreign key constraints
        $this->pdo->exec('SET foreign_key_checks = 1');
        // SERIALIZABLE isolation level
        $this->pdo->exec('SET TRANSACTION ISOLATION_LEVEL SERIALIZABLE');
        // Set UTF-8
        $this->pdo->exec("SET NAMES 'utf8'");
        $this->pdo->exec("SET CHARACTER SET utf8");
        $this->pdo->exec("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
    }

    public function getTables()
    {
    }

    public function getColumns($table)
    {
    }

    public function getCreate($table)
    {
    }
}

