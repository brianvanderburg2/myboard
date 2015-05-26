<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework\Database;


/**
 * A simple database exception.  This class is used for errors from the
 * database as well as our own errors.  Our own errors will not have
 * an error code, while errors from the database will use that error
 * code.
 *
 * \todo add support for error codes
 * \todo make general error code constants
 */
class Exception extends \mrbavii\Framework\Exception
{
    protected $_errorCode = null;

    /**
     * Construct the database exception
     *
     * \param $msg The message for the exception
     * \param $code The SQLSTATE code for the exception.  If omitted will be an empty string.
     * \param $previous The previous exception.
     */
    public function __construct($msg, $code='', $previous=null)
    {
        parent::__construct($msg, 0, $previous);
        $this->_errorCode = $code;
    }

    /**
     * Get the SQLSTATE error code for the exception
     */
    public function errorCode()
    {
        return $this->_errorCode;
    }
}

