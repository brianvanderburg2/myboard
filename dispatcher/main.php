<?php

// File:        main.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Main dispatcher

namespace mrbavii\MyBoard\Dispatcher;
use mrbavii\Framework;


// Must have an argument
if(count($path) == 0 || (count($path) == 1 && strlen($path[0]) == 0))
{
    $app->redirect("/index");
    exit();
}

// For this app, nothing should end with a '/' redirect without if needed
if(strlen($path[count($path) - 1]) == 0)
{
    array_pop($path);
    $app->redirect($path);
    exit();
}

// Check path components for security
foreach($path as $part)
{
    if(strlen($part) == 0 || !Framework\Security::checkPathComponent($part))
    {
        return; // 404
    }
}


// Check if already installed, redirect if not
if(!in_array($path[0], array("adminkey", "install", "upgrade", "resource")))
{
    $installer = $app->getService("installer");
    if(!$installer->isUpToDate())
    {
        if($installer->isInstalled())
        {
            $app->redirect("/upgrade");
        }
        else
        {
            $app->redirect("/install");
        }
        exit();
    }
}

$action = array_shift($path);

if($action == "adminkey" and count($path) == 0)
{
    // Show a page allowing to generate an admin key
    $pw = $request->post("password");

    $page = $app->getService("page");
    $page->set("title", "Create Admin Key");
    $page->set("key", ($pw !== null) ? $app->createAdminKey($pw) : FALSE);
    $page->send("admin.adminkey");
    exit();
}
else if($action == "resource")
{
    // Only allow for certain items
    if(count($path) == 0 || !in_array($path[0], ["images", "styles", "jscripts"]))
    {
        return;
    }

    // Build path
    $path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path);

    // Find the file
    foreach(["user", "app"] as $test)
    {
        $datadir = $app->getDataDir($test);
        if($datadir !== null && is_readable($datadir . $path))
        {
            $app->getService("response")->sendfile($datadir . $path);
            exit();
        }
    }

    // File not found
    return;
}

