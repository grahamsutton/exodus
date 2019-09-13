<?php

namespace Exodus\Database;

use Exodus\Database\Strategy;
use Exodus\Exception\UndefinedConfigParamException;

/**
 * The Database Adapter Interface
 *
 * Ensures that any future adapter implementations
 * do not force me to rewrite half the application.
 */
abstract class Adapter
{
    /**
     * A connection object or value that results from connecting
     * to a database.
     *
     * @var mixed
     */
    protected $conn;

    /**
     * The host of the database to connect to.
     *
     * @var string
     */
    protected $host;

    /**
     * The username of the database instance we want
     * to connect to.
     *
     * @var string
     */
    protected $username;

    /**
     * The password of the database instance we want to
     * connect to.
     *
     * @var string
     */
    protected $password;

    /**
     * The port number of the database instance we want
     * to connect to.
     *
     * @var string
     */
    protected $port;

    /**
     * Sets the name of the database that we want to connect
     * to.
     *
     * @var string
     */
    protected $database;

    /**
     * Constructor
     *
     * Sets the database parameters used for connecting to
     * database instances. These parameters should be defined
     * exodus.yml under db:*.
     *
     * @param array $db_params
     */
    public function __construct(array $db_params)
    {
        $this->setHost($db_params['host']);
        $this->setUsername($db_params['username']);
        $this->setPassword($db_params['password']);
        $this->setPort($db_params['port']);
        $this->setDatabase($db_params['name']);
    }

    /**
     * Sets the host name of the database instance to connect
     * to.
     *
     * @param string $host
     */
    public function setHost(string $host): void
    {
        if (empty($host)) {
            throw new UndefinedConfigParamException(
                'No host set for ' . implode(':', ['db', 'host']) . ' ' .
                'in exodus.yml'
            );
        }

        $this->host = $host;
    }

    /**
     * Returns the host name of the database instance to connect
     * to.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets the username of the database instance to connect to.
     *
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * Returns the username of the database instance to connect to.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Sets the password used to connect to the database instance.
     *
     * @param string|null $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * Returns the password used to connect to the database instance.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets the port number used for connecting to the database instance.
     *
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * Returns the port number used for connecting to the database instance.
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Sets the name of the database that we want to connect to.
     *
     * @param string $name
     */
    public function setDatabase(string $name): void
    {
        $this->database = $name;
    }

    /**
     * Returns the name of the database that we want to connect to.
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Runs the provided SQL query or script.
     *
     * @param string $query
     *
     * @return mixed
     */
    abstract public function query($query);

    /**
     * Connects to the database instance.
     *
     * @return void
     */
    abstract public function connect();

    /**
     * Closes the connection to the database instance.
     *
     * @return void
     */
    abstract public function close();

    /**
     * Begins a transaction.
     *
     * @return void
     */
    abstract public function begin();

    /**
     * Commits a transaction.
     *
     * @return void
     */
    abstract public function commit();

    /**
     * Rolls back a transaction.
     *
     * @return void
     */
    abstract public function rollback();

    /**
     * Performs a query, but assumes a connection is already
     * open.
     *
     * @param string $query
     */
    abstract public function execute($query);
}