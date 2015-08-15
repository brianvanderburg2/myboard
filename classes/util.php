<?php

// File:        util.php
// Author:      Brian Allen Vanderburg II
// Purpose:     The utility service for the board software

namespace mrbavii\MyBoard;

/**
 * The utiltiy service for the board software
 */
class Util
{
    const DBVERSION = 1; /**< Schema version for the database. */

    protected $board = null;
    protected $version = null;

    /**
     * Construct the installer object
     *
     * \param $board The instance of the board.
     */
    public function __construct($board)
    {
        $this->board = $board;
    }

    /**
     * Determine if the board is installed.
     *
     * \return TRUE if installed and up to date, FALSE otherwise.
     */
    public function isInstalled()
    {
        $version = $this->getVersion();

        return $version !== FALSE;
    }

    /**
     * Determine if the board tables is up to date.
     *
     * \return TRUE if the tables are up to date, FALSE otherwise.
     */
    public function isUpToDate()
    {
        $version = $this->getVersion();

        return $version === self::DBVERSION;
    }

    /**
     * Determine the board installer version.
     * This is the version of the table structures.
     *
     * \return The board version, or FALSE if not installed.
     */
    public function getVersion()
    {
        if($this->version !== null)
            return $this->version;

        $db = $this->board->getService("database")->connection("myboard");

        if(!$db->tableExists("myboard_settings"))
        {
            $this->version = FALSE;
        }
        else
        {
            $value = $db->query("SELECT value FROM myboard_settings WHERE name = 'database.version'")->fetchCol("value");
            if($value === FALSE)
            {
                $this->version = FALSE;
            }
            else
            {
                $this->version = intval($value);
            }
        }

        return $this->version;
    }

    /**
     * Get a list of all the board tables.
     *
     * \return Array of table names.  The names are not prefixed.
     */
    public function getTables()
    {
        return array(
            "settings"
        );
    }
}

