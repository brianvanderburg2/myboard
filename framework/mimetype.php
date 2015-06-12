<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */

namespace mrbavii\Framework;

/**
 * This is a MIME types helper class.
 */
class MimeType
{
    protected $app;

    /**
     * Construct the mime type helper.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Determine the content type for a given file.
     */
    public function getContentType($filename, $magic=null)
    {
        /** \todo: document config values */

        // Use file extensions if we can
        if($magic === null || $magic === FALSE)
        {
            // Find the longest match
            $types = array_merge($this->defaultTypes(), $this->app->getConfig("mime.types", array()));
            $match = FALSE;

            foreach($types as $ending => $type)
            {
                if(Util::endsWith($filename, $ending, TRUE))
                {
                    if($match === FALSE || strlen($ending) > strlen($match))
                    {
                        $match = $ending;
                    }
                }
            }

            if($match !== FALSE)
            {
                return $types[$match];
            }
        }

        // Use MIME magic if we can
        if(($magic === null || $magic === TRUE) && 
            is_readable($filename) &&
            $this->app->getConfig("mime.magic.enabled", TRUE))
        {
            $magicfile = $this->app->getConfig("mime.magic.file");
            if($magicfile === null)
            {
                $fi = new \finfo(FILEINFO_MIME_TYPE);
            }
            else
            {
                $fi = new \finfo(FILEINFO_MIME_TYPE, $magicfile);
            }

            return $fi->file($filename);
        }

        return FALSE;
    }

    /**
     * Return an internal list of default types.
     */
    protected function defaultTypes()
    {
        return array(
            // Text
            ".css" => "text/css",
            ".js" => "text/javascript",

            // Images
            ".jpg" => "image/jpeg",
            ".jpeg" => "image/jpeg",
            ".png" => "image/png"
        );
    }
}
