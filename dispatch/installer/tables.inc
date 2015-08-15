<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 *
 * This file contains table definitions used for MyBoard.  It gets included
 * by installer.php using the loadPhp method of the application and returns
 * the list of table definitions.
 */

$props = [];
$tables = [];
$constraints = [];

// Properties (applied to all tables)
$props[] = "ENGINE = InnoDB";
$props[] = "DEFAULT CHARACTER SET 'utf8mb4'"; // Causes text to use 4 bytes/char

// Tables and constraints
// Tables are stored as an associative array of table name to columns
// The columns are associative array of column names to definitions.
// This allows other code to know which tables exist and to know what
// the columns are without trying to parse anything.
//
// $all_tables = array_keys($tables);
// $all_columns = array_keys($tables[$name]);


// myboard_settings
$tables["myboard_settings"] = [
    "name" => "VARCHAR(63)",
    "value" => "VARCHAR(63)"
];
$constraints["myboard_settings"] = [
    "PRIMARY KEY(name)"
];

// myboard_groups
$tables["myboard_groups"] = [
    "groupid" => "BIGINT",
    "groupname" => "VARCHAR(63) NOT NULL",
    "description" => "VARCHAR(255)",
    "joinable" => "TINYINT DEFAULT 0",
    "inviteonly" => "TINYINT DEFAULT 1",
];
$constraints["myboard_groups"] = [
    "PRIMARY KEY(groupid)",
    "UNIQUE KEY(groupname)"
];

// myboard_users
$tables["myboard_users"] = [
    "userid" => "BIGINT",
    "username" => "VARCHAR(63)",
    "password" => "VARCHAR(255)",
    "loginkey" => "VARCHAR(255)",
    "activation" => "VARCHAR(255)",
    "groupid" => "BIGINT",
    "regdate" => "BIGINT",
    "regip" => "VARCHAR(45)", // Max text length of IPv4/IPv6 address.
    "lastip" => "VARCHAR(45)",
    "lastvisit" => "BIGINT", // Last login
    "lastactive" => "BIGINT", // Last activity such as post
    "email" => "VARCHAR(255)",
    "emailprivate" => "TINYINT",
    "birthday" => "BIGINT",
    "birthdayprivate" => "TINYINT",
    "timezone" => "TINYINT",
    "timeadjust" => "INT",
    "loginattempts" => "TINYINT",
    "bancount" => "INT",
    "bantime" => "BIGINT",
    "warnings" => "INT",
    "usernotes" => "TEXT"
];
$constraints["myboard_users"] = [
    "PRIMARY KEY(userid)",
    "UNIQUE KEY(username)",
    "CONSTRAINT fk_groupid FOREIGN KEY(groupid) REFERENCES myboard_groups(groupid)"
];



// Return all that stuff
return [$tables, $constraints, $props];

