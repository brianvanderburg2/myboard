<?php

// File:        main.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Main dispatcher

namespace mrbavii\MyBoard\Dispatch;
use mrbavii\Framework;
use mrbavii\MyBoard;

class Main extends Framework\Dispatcher
{
    /**
     * Our custom dispatcher performs some initial checks first.
     */
    public function dispatch($request, $path)
    {
        // Must have an argument
        if(count($path) == 0 || (count($path) == 1 && strlen($path[0]) == 0))
        {
            $this->app->redirect("/index");
            exit();
        }

        // For this app, nothing should end with a '/' redirect without if needed
        if(strlen($path[count($path) - 1]) == 0)
        {
            array_pop($path);
            $this->app->redirect($path);
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
            $util = $this->app->getService("util");
            if(!$util->isUpToDate())
            {
                if($util->isInstalled())
                {
                    $this->app->redirect("/upgrade");
                }
                else
                {
                    $this->app->redirect("/install");
                }
                exit();
            }
        }

        // Handle normally
        return parent::dispatch($request, $path);
    }

    // Handle admin key
    public function dispatch_adminkey($request, $path)
    {
        // Show a page allowing to generate an admin key
        $pw = $request->post("password");

        $page = new MyBoard\Page($this->app);
        $page->set("title", "Create Admin Key");
        $page->set("key", ($pw !== null) ? $this->app->createAdminKey($pw) : FALSE);
        $page->send("/admin/adminkey");
        exit();
    }

    // Handle install
    public function dispatch_install($request, $path)
    {
        $obj = new Installer($this->app);
        return $obj->dispatch($request, $path);
    }

    // Handle upgrade
    public function dispatch_upgrade($request, $path)
    {
        $obj = new Upgrader($this->app);
        return $obj->dispatch($request, $path);
    }

    // Handle resources
    public function dispatch_resource($request, $path)
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
            $datadir = $this->app->getDataDir($test);
            if($datadir !== null && is_readable($datadir . $path))
            {
                $this->app->getService("response")->sendfile($datadir . $path);
                exit();
            }
        }

        // File not found
        return;
    }
}

