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
 * SQLite3 helper object
 */
class Helper_sqlite3
{
    // Perform post-connect actions
    public function postConnect()
    {
        // Enable foreign key constraints
        $this->pdo->exec("PRAGMA foreign_keys = 1");
        // SERIALIZABLE is the default already
        $this->pdo->exec("PRAGMA read_uncommitted = 0");
    }
}

