<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework\Database;
use mrbavii\Framework\Exception;

/**
 * A helper object
 */
class Helper_base
{
    protected $pdo = null;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Isolation level support
    public function hasIsolationLevel()
    {
        return FALSE;
    }

    public function setIsolationLevel()
    {
        throw new Exception('Error : Not implemented');
    }

    // Table information
    public function getTables()
    {
        throw new Exception('Error : Not implemented');
    }

    public function getColumns($table)
    {
        throw new Exception('Error : Not implemented');
    }

    public function getCreaet($table)
    {
        throw new Exception('Error : Not implemented');
    }

    // Savepoints (used for nested transaction emulation)
    public function hasSavePoint()
    {
        return FALSE;
    }

    public function createSavePoint($name)
    {
        $this->pdo->exec("SAVEPOINT $name");
    }

    public function releaseSavePoint($name)
    {
        $this->pdo->exec("RELEASE SAVEPOINT $name");
    }

    public function rollbackSavePoint($name)
    {
        $this->pdo->exec("ROLLBACK TO SAVEPOINT $name");
    }
}

