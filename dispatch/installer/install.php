<?php

// Install the MyBoard software.

// Steps:
// 1. Gather information from the user
// 2. Create database tables
// 3. Populate database tables with data
// 4. Apply information gathered from step 1

$action = $request->get("action");
if($action === null)
{
    $page = $app->getService("page");
    $page->set("title", "Install - Configuration");
    $page->send("admin.install.config");
    exit();
}
else if($action == "install")
{
    $db = $app->getService("database")->connection("myboard");
    list($tables, $constraints, $props) = $app->loadPhp(__DIR__ . "/tables.inc");

    foreach($tables as $table => $cols)
    {
        // Drop the table first
        $sql = "DROP TABLE IF EXISTS {$table}";
        $db->execute($sql);

        // Now create the table
        $colsql = [];
        foreach($cols as $col => $coldef)
        {
            $colsql[] = $col . " " . $coldef;
        }
        $sql = "CREATE TABLE {$table} (";
        $sql .= implode(",", $colsql);

        if(isset($constraints[$table]))
        {
            $sql .= "," . implode(",", $constraints[$table]);
        }

        $sql .= ")";
        if(count($props))
        {
            $sql .= " " . implode(",", $props);
        }
        echo $sql;
        echo "<br/>";
        var_dump(($db->execute($sql)));
    }

    $page = $app->getService("page");
    $page->set("title", "Install - Finished");
    //$page->send("admin.install.finished");
}

