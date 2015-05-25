<?php

/**
 * \file
 * \author      Brian Allen Vanderburg II
 * \date        2015
 * \copyright   MIT License
 */


namespace mrbavii\Framework;

/**
 * A PDO database wrapper with some extra functions.
 */
class Database
{
    protected $app = null;
    protected $connected = FALSE;
    protected $helper = null;
    protected $pdo = null;
    protected $prefix = '';
    protected $transCounter = 0;

    /**
     * Construct the database object.
     * This does not connect the database, but only sets the application
     * and configuration for the database.
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Connect to a database.
     *
     * \param $config The databasse to connect to.
     *  - If null, get configuration from database.default then continue to the next step.
     *  - If a string, get configuration from database.connections.$config
     *  - If an array, use $config as the database configuration
     */
    public function connect($config=null)
    {
        // Only connect once
        if($this->connected)
        {
            throw new Exception('Connect error : Connection already established');
        }

        // Determine default confgiration if needed
        if($config === null)
        {
            $config = $this->app->getConfig('database.default');
            if($config === null)
            {
                throw new Exception('Connect error : No default connection');
            }
        }

        // If the result was a string, look up the config
        if(is_string($config))
        {
            $tmp = $this->app->getConfig('database.connections.' . $config);
            if($tmp === null)
            {
                throw new Exception('Connect error : No named connection : ' . $config);
            }
            $config = $tmp;
        }

        // Determine information
        if(!isset($config['dsn']))
        {
            throw new Exception('Connect error : Database configuration without dsn');
        }

        $dsn = $config['dsn'];
        $username = isset($config['username']) ? $config['username'] : null;
        $password = isset($config['password']) ? $config['password'] : null;
        $options = isset($config['options']) ? $config['options'] : array();

        // Combine our custom options
        $options = array_merge($options, array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ));

        // Connect
        $this->pdo = new \PDO($dsn, $username, $password, $options);
        $this->connected = TRUE;

        // Create our helper if we can
        $driver_name = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        $helper_class = __NAMESPACE__ . '\\Database\\Helper_' . strtolower($driver_name);
        if(class_exists($helper_class))
        {
            $this->helper = new $helper_class($this->pdo);
        }

        // Isolation level
        if(isset($config['isolation']))
        {
            $this->setIsolationLevel($config['isolation']);
        }

        // Table prefix
        if(isset($config['prefix'])
        {
            $this->setPrefix($config['prefix']);
        }
    }

    /**
     * Get the helper if there is one.
     *
     * \return The helper or null.
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get the PDO instance
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * Set the isolation level
     *
     * \param $level The isolation level to set to, one of 'SERIALIZABLE', 'REPEATABLE_READ', 'READ_COMMITTED', or 'READ_UNCOMMITTED'
     * \note This will fail if there is no helper or the helper does not support isolation levels.
     */
    public function setIsolationLevel($level)
    {
        if($this->helper === null || !$this->helper->hasIsolationLevel())
        {
            throw new Exception('Connect error : Unable to set isolation level');
        }

        switch(strtolower(str_replace(array(' ', '_'), '', $level)))
        {
            case 'SERIALIZABLE':
            default:
                $this->helper->setIsolationLevel(0);
                break;

            case 'REPEATABLEREAD':
                $this->helper->setIsolationLevel(1);
                break;

            case 'READCOMMITTED':
                $this->helper->setIsolationLevel(2);
                break;

            case 'READUNCOMMITTED':
                $this->helper->setIsolationLevel(3);
                break;
        }
    }

    /**
     * Set the table prefix.
     *
     * \param $prefix The table prefix.
     * \note This only stores the table prefix.  It is not used internally, but may be fetched with getPrefix
     *       and used in queries.
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the table prefix.
     *
     * \return The current table prefix.
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get a list of all tables.
     *
     * \return A list of all tables.
     * \note This will fail if there is no helper.
     */
    public function getTables()
    {
        if($this->helper === null)
        {
            throw new Exception('Database error : Unable to retrieve table names')
        }

        return $this->helper->getTables();
    }

    /**
     * Get a list of all columns in a table.
     *
     * \return The column list.
     * \note This will fail if there is no helper.
     */
    public function getColumns($table)
    {
        if($this->helper === null)
        {
            throw new Exception('Database error : Unable to retrieve column names');
        }

        return $this->helper->getColumns($table);
    }

    /**
     * Get the create syntax for the table.
     *
     * \return the SQL create statement.
     * \note This wil fail if there is no helper.
     */
    public function getCreate($table)
    {
        if($this->helper === null)
        {
            throw new Exception('Database error : Unable to retrieve table create command');
        }

        return $this->helper->getColumns($table);
    }

    /**
     * Determine if currently in a transaction.
     */
    public function inTransaction()
    {
        return $this->pdo->inTransation();
    }

    /**
     * Begin a transaction.
     */
    public function beginTransaction()
    {
        if($this->transCounter == 0 || $this->helper == null || !$this->helper->hasSavePoint())
        {
            $this->pdo->beginTransaction();
        }
        else
        {
            $this->helper->createSavePoint("LEVEL${this->transCounter}");
        }

        $this->transCounter++;
    }

    /**
     * Commit a transaction
     */
    public function commit()
    {
        if($this->transCounter == 0)
        {
            throw new Exception('Rollback error : There is no transaction started.')
        }

        $this->transCounter--;

        if($this->transCounter == 0 || $this->helper === null || !$this->helper->hasSavePoint())
        {
            $this->pdo->commit();
        }
        else
        {
            $this->helper->releaseSavePoint("LEVEL{$this->transCounter}");
        }
    }

    /**
     * Rollback a transaction.
     */
    public function rollback()
    {
        if($this->transCounter == 0)
        {
            throw new Exception('Rollback error : There is no transaction started.')
        }

        $this->transCounter--;

        if($this->transCounter == 0 || $this->helper == null || !$this->helper->hasSavePoint())
        {
            $this->pdo->rollback();
        }
        else
        {
            $this->helper->rollbackSavePoint("LEVEL{$this->transCounter}");
        }
    }

    // Passtrhu functions
    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    public function execute($statement, $params=null)
    {
        if($params === null)
        {
            return $this->pdo->exec($statement);
        }
        else
        {
            $stmt = $this->pdo->prepare($statement);
            $stmt->execute($params);
            $count = $stmt->rowCount();
            $stmt->closeCursor();

            return $stmt;
        }
    }

    public function query($statement, $params=null)
    {
        if($params === null)
        {
            $stmt = $this->pdo->query($statement);
        }
        else
        {
            $stmt = $this->pdo->prepare($statement);
            $stmt->execute($params);
        }

        return new _DatabaseStatement($this->pdo, $stmt);
    }

    public function prepare($statement)
    {
        $stmt = $this->pdo->prepare($statemetn);
        return new _DatabaseStatement($this->pdo, $stmt);
    }

    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    public function quote($string, $type=\PDO::PARAM_STR)
    {
        return $this->pdo->quote($string, $type);
    }
}


/**
 * Wrapper for PDOStatement
 */
class _DatabaseStatement
{
    protected $pdo = null;
    protected $stmt = null;

    public function __construct($pdo, $stmt)
    {
        $this->pdo = $pdo;
        $this->stmt = $stmt;
    }

    // For now, map all method calls to stmt
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->stmt, $name), $args);
    }

    public function __get($name)
    {
        return $this->stmt->{$name};
    }
}




