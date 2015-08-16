<?php

// Install the MyBoard software.

use mrbavii\Framework;
use mrbavii\MyBoard;

// Steps:
// 1. Gather information from the user
// 2. Create database tables
// 3. Populate database tables with data
// 4. Apply information gathered from step 1

$action = $request->get("action");
if($action === null)
{
    $page = new MyBoard\Page($app);
    $page->set("title", "Install - Configuration");
    $page->send("admin.install.config");
    exit();
}
else if($action == "install")
{
    $db = $app->getService("database")->connection();
    list($tables, $constraints, $props) = $app->loadPhp(__DIR__ . "/tables.inc");


    // Drop tables in reverse to avoid foreign key issues.
    foreach(array_reverse(array_keys($tables)) as $table)
    {
        $sql = "DROP TABLE IF EXISTS {$table}";
        $db->execute($sql);
    }

    // Now create the tables forward
    foreach($tables as $table => $cols)
    {
        $colsql = [];
        foreach($cols as $col => $coldef)
        {
            $colsql[] = "    " . $col . " " . $coldef;
        }
        $sql = "CREATE TABLE {$table} (\n";
        $sql .= implode(",\n", $colsql);

        if(isset($constraints[$table]))
        {
            $sql .= ",\n    " . implode(",\n    ", $constraints[$table]);
        }

        $sql .= "\n)";
        if(count($props))
        {
            $sql .= " " . implode(", ", $props);
        }
        $db->execute($sql);
    }

    $page = new MyBoard\Page($app);
    $page->set("title", "Install - Finished");
    //$page->send("admin.install.finished");
}

