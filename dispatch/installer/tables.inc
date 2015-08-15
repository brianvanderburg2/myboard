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
$props[] = "DEFAULT CHARACTER SET 'utf8mb4'";

// Tables and constraints

// settings
$tables["myboard_settings"] = [
    "name" => "VARCHAR(63)",
    "value" => "VARCHAR(63)"
];
$constraints["myboard_settings"] = [
    "PRIMARY KEY(name)"
];



// Return all that stuff
return [$tables, $constraints, $props];

