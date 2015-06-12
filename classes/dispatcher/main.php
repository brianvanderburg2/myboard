<?php

// File:        main.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Main dispatcher

namespace mrbavii\MyBoard\Dispatcher;
use mrbavii\Framework;

class Main extends Framework\Dispatcher
{
    public function dispatch($request, $path)
    {
        if(count($path))
        {
            if(!in_array($path[0], array("adminkey", "install", "upgrade", "resource")))
            {
                $installer = $this->app->getService("installer");
                if(!$installer->isUpToDate())
                {
                    if($installer->isInstalled())
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
        }

        parent::dispatch($request, $path);
    }

    public function dispatch_adminkey($request, $path)
    {
        if(count($path) > 0)
        {
            return;
        }

        // Show a page allowing to generate an admin key
        $pw = $request->post("password");

        $page = $this->app->getService("page");
        $page->set("title", "Create Admin Key");
        $page->set("key", ($pw !== null) ? $this->app->createAdminKey($pw) : FALSE);
        $page->send("admin/adminkey");

        exit();
    }

    public function dispatch_upgrade($request, $path)
    {
    }

    public function dispatch_install($request, $path)
    {
    }

    public function dispatch_resource($request,  $path)
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

