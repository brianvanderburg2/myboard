<?php

/**
 * \file
 * \author      Brian Allen Vanderburg Ii
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework\Database;

/**
 * PDO driver object
 */
abstract class Driver_pdo extends Driver
{
    protected $pdo = null;

    // Construct the PDO connection
    public function __construct($app, $config)
    {
        // Make sure DSN is specified
        if(!isset($config['dsn']))
        {
            throw new Exception('No DSN specified');
        }

        // Get information
        $dsn = $config['dsn'];
        $username = isset($config['username']) ? $config['username'] : null;
        $password = isset($config['password']) ? $config['password'] : null;
        $options = isset($config['options']) ? $config['options'] : array();

        $options = array_merge($options, array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));

        // Update config
        $config['options'] = $options;

        // Construct parent
        parent::__construct($app, $config);

        // Connect
        try
        {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    // Transaction functions
    public function inTransaction()
    {
        try
        {
            return $this->pdo->inTransaction();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function begin()
    {
        try
        {
            return $this->pdo->beginTransaction();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function commit()
    {
        try
        {
            return $this->pdo->commit();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function rollback()
    {
        try
        {
            return $this->pdo->rollback();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    // Query functions
    public function execute($statement, $params=null)
    {
        try
        {
            if($params === null)
            {
                return $this->pdo->exec($statement);
            }
            else
            {
                $query = $this->prepare($statement);
                $query->execute($params);
                $rowCount = $query->rowCount();
                $query->close();
                return $rowCount;
            }
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function query($statement, $params=null)
    {
        try
        {
            if($params === null)
            {
                $stmt = $this->pdo->query($statement);
                return new Query_pdo($this->pdo, $stmt);
            }
            else
            {
                $query = $this->prepare($statement);
                $query->execute($params);
                return $query;
            }
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function prepare($statement)
    {
        try
        {
            $stmt = $this->pdo->prepare($statement);
            return new Query_pdo($this->pdo, $stmt);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    // Information
    public function lastInsertId($seq=null)
    {
        try
        {
            return $this->pdo->lastInsertId($seq);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function quote($string)
    {
        try
        {
            return $this->pdo->quote($string);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}

