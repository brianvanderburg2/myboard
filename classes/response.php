<?php

// File:        response.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Handle sending responses back to the browser.

namespace MyBoard;

/**
 * A class for sending responses back to the browser.
 */
class Response
{
    protected $board;

    public function __construct($board)
    {
        $this->board = board;
    }

    /**
     * Disable caching of pages.
     */
    public function noCache()
    {
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 86400) . ' GMT');
    }

    /**
     * Enable caching of pages.
     *
     * @param delta The maximum number of seconds the page should be cached.
     * @param private If the cache should be considered private.
     */
    public function cache($delta=0, $private=TRUE)
    {
        $visibility = $private ? 'private' : 'public';
        $duration = ($delta > 0) ? ", max-age=$delta" : '';

        header("Cache-Control: {$visibility}{$duration}");
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $delta) . ' GMT');
    }
    
    /**
     * Redirect to another location.
     *
     * @param url An absolute URL either with or without the domain and
     *  protocol.  If specified as '/path/to/file', the host, protocol,
     *  and port number will automatically be added.  If no colon is present
     *  and it does not begin with a '/', then the redirect based on the
     *  script entry point.
     * @return This method does not return.
     */
    public function redirect($url, $code=303)
    {
        if(strlen($url) >= 2 && substr($url, 0, 2) == '//')
        {
            // Begin with '//' means scheme relative to another domain
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            {
                $proto = 'https:';
            }
            else
            {
                $proto = 'http:';
            }

            $url = $proto . $url;
        }
        else if(strpos($url, ':') == FALSE)
        {
            // No colon means using current domain
            if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            {
                $proto = 'https://';
            }
            else
            {
                $proto = 'http://';
            }

            if(isset($_SERVER['HTTP_HOST']))
            {
                // HTTP_HOST includes port if it was used
                $server = $_SERVER['HTTP_HOST'];
            }
            else
            {
                // SERVER_NAME does not include port even if used
                $server = $_SERVER['SERVER_NAME'];
                if(isset($_SERVER['SERVER_PORT']))
                {
                    $server = $server. ':' . $_SERVER['SERVER_PORT'];
                }
            }

            // If url does not begin with '/' like '/path/to/file' use REQUEST_URI 
            if($url[0] != '/')
            {
                // Add to REQUEST_URI
                $prefix = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                if(substr($prefix, -1) != '/')
                {
                    // We are not a directory, so append url to the directory part only
                    $pos = strrpos($prefix, '/');
                    if($pos !== FALSE)
                    {
                        $prefix = substr($prefix, 0, $pos + 1);
                    }
                    else
                    {
                        $prefix = '/';
                    }
                }

                $url = $prefix . $url;
            }

            $url = $proto . $server . $url;
        }

        $this->status($code);
        $this->noCache();
        header('Location: '.$url, TRUE, $code);
        exit();
    }

    /**
     * Set the status header.
     *
     * @param status The status to set.
     * @param desc The description of the status.
     */
    public function status($status, $desc=null)
    {
        $str = "$status";
        if($desc !== null)
            $str .= " $desc";

        if(substr(php_sapi_name(), 0, 3) == 'cgi')
        {
            header("Status: $str");
        }
        else
        {
            header("{$_SERVER['SERVER_PROTOCOL']} $str");
        }
    }

    /**
     * Send a file
     *
     * @param $file The relative path of the file to send
     * @param $cache The time the client should cache this file
     * @param $private Request any middle-man caches to not cache the result if true.
     */
    public function sendFile($file, $cache=0, $private=FALSE)
    {
        // Security check
        if(!Security::checkPath($file))
        {
            $this->board->notfound();
            exit();
        }

        // Determine if we are sending user or app data.
        if(file_exists($this->board->userdata.dir . '/' . $file))
        {
            $isuser = TRUE;
            $filename = $this->board->userdata.dir . '/' . $file; 
        }
        else if(file_exists($this->board->appdata.dir . '/' . $file))
        {
            $isuser = FALSE;
            $filename = $this->board->appdata.dir . '/' . $file;
        }
        else
        {
            $this->board->notfound();
            exit();
        }

        // Handle if-modified-since header
        $file_timestamp = filemtime($filename);
        if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
        {
            $if_modified_since = strtotime(preg_replace('#;.*$#', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']));
            if($if_modified_since >= $file_timestamp)
            {
                $this->status(304, 'Not Modified');
                exit();
            }
        }
        
        // Set headers: Content-Type, Content-Length, Content-Disposition
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $file_timestamp));
        header('Content-Length: ' . filesize($file));

        $fi = new \finfo(FILEINFO_NONE, Util::arrayGet($this->board->config, 'mime.magicfile'));
        $type = $fi->file($filename, FILEINFO_MIME_TYPE);
        if($type === FALSE)
            $type = 'application/octed-stream';

        header('Content-Type: ' . $type);

        if($cache == 0)
        {
            $this->noCache();
        }
        else
        {
            $this->cache($cache, $private);
        }
        

        // Only proceed if needed
        if($this->board->request->method == 'head')
            exit();

        // Send the file through
        while(@ob_end_flush());

        if($isuser && $this->board->userdata.callback)
        {
            call_user_func($this->board->userdata.callback($file, $filename));
            exit();
        }
        else if(!$isuser && $this->board->appdata.callback)
        {
            call_user_func($this->board->appdata.callback($file, $filename));
            exit();
        }

        // Got here, manually send using readfile
        @readfile($filename, FALSE);
        exit();
    }
}

