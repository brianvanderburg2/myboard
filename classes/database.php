<?php

// File:        database.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple database access layer

namespace MyBoard;

class Database extends \PDO
{
    public $prefix = null;

    public function __construct($board)
    {
        $config = $board->config;

        $dsn = "mysql:";

        // host/port/socket
        $socket = Util::arrayGet($config, 'database.socket', null, 'unix_socket=');
        if($socket)
        {
            $dsn .= $socket;
        }
        else
        {
            $dsn .= Util::arrayGet($config, 'database.host', 'host=localhost', 'host=');
            $dsn .= Util::arrayGet($config, 'database.port', '', ';port=');
        }

        // database name
        $dsn .= Util::arrayGet($config, 'database.name', '', ';dbname=');

        // username and password
        $user = Util::arrayGet($config, 'database.user');
        $pass = Util::arrayGet($config, 'database.pass');
        $board->config['database.pass'] = "";

        // database table prefix
        $this->prefix = Util::arrayGet($config, 'database.prefix', 'myboard_');

        // connect to db and perform initial setup
        parent::__construct($dsn, $user, $pass, array(
            \PDO::ATTR_PERSISTENT => true
        ));

        $this->exec('SET NAMES utf8');
    }
}

