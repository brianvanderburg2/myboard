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
            if(!in_array($path[0], array("install", "upgrade", "resource")))
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

        return parent::dispatch($request, $path);
    }

    public function dispatch_upgrade($request, $path)
    {
        return FALSE;
    }

    public function dispatch_install($request, $path)
    {
        return FALSE;
    }

    public function dispatch_resource($request,  $path)
    {
        return FALSE;
    }
}

