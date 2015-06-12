<?php

// File:        response.php
// Author:      Brian Allen Vanderburg II
// Purpose:     Handle sending responses back to the browser.

namespace mrbavii\Framework;

/**
 * A class for sending responses back to the browser.
 */
class Response
{
    protected $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Disable caching of pages.
     */
    public function noCache()
    {
        header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() - 86400) . " GMT");
    }

    /**
     * Enable caching of pages.
     *
     * \param delta The maximum number of seconds the page should be cached.
     * \param private If the cache should be considered private.
     */
    public function cache($delta=0, $private=TRUE)
    {
        $visibility = $private ? "private" : "public";
        $duration = ($delta > 0) ? ", max-age=$delta" : "";

        header("Cache-Control: {$visibility}{$duration}");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $delta) . " GMT");
    }
    
    /**
     * Redirect to another location.
     *
     * \param url An absolute URL either with or without the domain and
     *  protocol.  If specified as "/path/to/file", the host, protocol,
     *  and port number will automatically be added.  If no colon is present
     *  and it does not begin with a "/", then the redirect based on the
     *  script entry point.
     * \return This method does not return.
     */
    public function redirect($url, $code=303)
    {
        if(strlen($url) >= 2 && substr($url, 0, 2) == "//")
        {
            // Begin with "//" means scheme relative to another domain
            if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
            {
                $proto = "https:";
            }
            else
            {
                $proto = "http:";
            }

            $url = $proto . $url;
        }
        else if(strpos($url, ":") == FALSE)
        {
            // No colon means using current domain
            if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
            {
                $proto = "https://";
            }
            else
            {
                $proto = "http://";
            }

            if(isset($_SERVER["HTTP_HOST"]))
            {
                // HTTP_HOST includes port if it was used
                $server = $_SERVER["HTTP_HOST"];
            }
            else
            {
                // SERVER_NAME does not include port even if used
                $server = $_SERVER["SERVER_NAME"];
                if(isset($_SERVER["SERVER_PORT"]))
                {
                    $server = $server. ":" . $_SERVER["SERVER_PORT"];
                }
            }

            // If url does not begin with "/" like "/path/to/file" use REQUEST_URI 
            if($url[0] != "/")
            {
                // Add to REQUEST_URI
                $prefix = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

                if(substr($prefix, -1) != "/")
                {
                    // We are not a directory, so append url to the directory part only
                    $pos = strrpos($prefix, "/");
                    if($pos !== FALSE)
                    {
                        $prefix = substr($prefix, 0, $pos + 1);
                    }
                    else
                    {
                        $prefix = "/";
                    }
                }

                $url = $prefix . $url;
            }

            $url = $proto . $server . $url;
        }

        $this->status($code);
        $this->noCache();
        header("Location: ".$url, TRUE, $code);
        exit();
    }

    /**
     * Set the status header.
     *
     * \param status The status to set.
     * \param desc The description of the status.
     */
    public function status($status, $desc=null)
    {
        $str = "$status";
        if($desc !== null)
            $str .= " $desc";

        if(substr(php_sapi_name(), 0, 3) == "cgi")
        {
            header("Status: $str");
        }
        else
        {
            header("{$_SERVER["SERVER_PROTOCOL"]} $str");
        }
    }


    /**
     * Handle If-Modified-Since headers.
     * This function will check if the If-Modified-Headers (if set) are greater than
     * a certain timestamp.  If so, then it will set the 304 Not Modified status
     * and return true.  Otherwise it will set the Last-Modified header to the timestamp
     * passed in and return false;
     *
     * \param $timestamp the timestamp of the content in question
     * \return
     *   - TRUE if the $timestamp is greater than that of the If-Modified-Since header
     *   - TRUE if there are no If-Modified-Since headers
     *   - FALSE if the $timestamp is not greater than that of the If-Modified-Since header
     */
    public function ifModifiedSince($timestamp)
    {
        // Handle if-modified-since header
        if(isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]))
        {
            $if_modified_since = strtotime(preg_replace("#;.*$#", "", $_SERVER["HTTP_IF_MODIFIED_SINCE"]));
            if($if_modified_since >= $timestamp)
            {
                $this->status(304, "Not Modified");
                return FALSE;
            }
        }
        
        // Set headers: Content-Type, Content-Length, Content-Disposition
        header("Last-Modified: " . gmdate("D, d M Y H:i:s \G\M\T", $timestamp));
        return TRUE;
    }

    /**
     * Send a file.
     * \warning No security checks are preformed on the path here.
     *
     * \param $filename The path of the file to send
     * \param $cache The time the client should cache this file
     * \param $private Request any middle-man caches to not cache the result if true.
     */
    public function sendFile($filename, $cache=0, $private=FALSE)
    {
        // Handle if-modified-since header
        $file_timestamp = filemtime($filename);
        if($this->ifModifiedSince($file_timestamp) == FALSE)
        {
            exit();
        }
        
        // Set headers: Content-Type, Content-Length, Content-Disposition
        header("Content-Length: " . filesize($filename));

        $fi = new \finfo(FILEINFO_NONE); // TODO: allow configuration of mime file used
        $type = $fi->file($filename, FILEINFO_MIME_TYPE);
        if($type === FALSE)
            $type = "application/octet-stream";

        header("Content-Type: " . $type);

        if($cache == 0)
        {
            $this->noCache();
        }
        else
        {
            $this->cache($cache, $private);
        }

        // Only proceed if needed
        if($this->app->getService("request")->method() == "head")
            exit();

        // Send the file through
        while(@ob_end_flush());

        // TODO: allow configuration of a callback function to send the file.
        // or a sendfile helper that can be configured, perhaps Sendfile\Driver_<name>
        // for xsendfile, nginx, etc

        // Got here, manually send using readfile
        @readfile($filename, FALSE);
        exit();
    }
}

