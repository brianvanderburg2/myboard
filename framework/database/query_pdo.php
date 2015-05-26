<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework\Database;


/**
 * PDO database query
 */
class Query_pdo
{
    protected $pdo = null;
    protected $stmt = null;

    public function __construct($pdo, $stmt)
    {
        parent::__construct();

        $this->pdo = $pdo;
        $this->stmt = $stmt;
    }

    public function close()
    {
        try
        {
            $this->pdo->closeCursor();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fetch()
    {
        try
        {
            return $this->stmt->fetch(\PDO::FETCH_BOTH);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fetchAll()
    {
        try
        {
            return $this->stmt->fetchAll(\PDO::FETCH_BOTH);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function execute($params=null)
    {
        try
        {
            $this->stmt->execute($params);
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function rowCount()
    {
        try
        {
            return $this->stmt->rowCount();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function lastInsertId()
    {
        try
        {
            return $this->pdo->lastInsertId();
        }
        catch(\PDOException $e)
        {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
}

