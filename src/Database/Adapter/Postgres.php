<?php

namespace Exodus\Database\Adapter;

use Exodus\Database\Adapter;
use Exodus\Exception\NoDatabaseConnectionException;
use Exodus\Exception\PostgresQueryException;

/**
 * The Postgres Database Adapter
 *
 * The database adapter used to connect with PostgreSQL.
 */
class Postgres extends Adapter
{
    /**
     * Runs a query via Postgres.
     *
     * @param string $query
     *
     * @return mixed
     */
    public function query($query)
    {
        $this->connect();

        $response = $this->execute($query);

        $this->close();

        return $response;
    }

    /**
     * Connects to the Postgres instance.
     *
     * @return void
     */
    public function connect()
    {
        $this->conn = pg_connect(
            "host=$this->host " .
            "port=$this->port " .
            "dbname=$this->database " . 
            "user=$this->username " . 
            "password=$this->password"
        );
    }

    /**
     * Close the connection to the Postgres instance.
     *
     * @return void
     */
    public function close()
    {
        if ($this->conn) {
            pg_close($this->conn);
        }

        $this->conn = null;
    }

    /**
     * Begins a transaction.
     *
     * @return void
     */
    public function begin()
    {
        if (!$this->conn) {
            $this->connect();
        }

        pg_query($this->conn, 'BEGIN');
    }

    /**
     * Commits a transaction.
     *
     * @return void
     */
    public function commit()
    {
        if (!$this->conn) {
            throw new NoDatabaseConnectionException(
                'Cannot commit transaction. No database connection found.'
            );
        }

        pg_query($this->conn, 'COMMIT');

        $this->close();
    }

    /**
     * Performs a roll back of a transaction.
     *
     * @return void
     */
    public function rollback()
    {
        if (!$this->conn) {
            throw new NoDatabaseConnectionException(
                'Cannot commit transaction. No database connection found.'
            );
        }

        pg_query($this->conn, 'ROLLBACK');

        $this->close();
    }

    /**
     * Performs a query, but assumes that a connection is already open.
     *
     * Use this method instead of query() when executing within a 
     * transaction.
     *
     * @return mixed
     */
    public function execute($query)
    {
        if (!$this->conn) {
            throw new NoDatabaseConnectionException(
                'No Postgres database connection is active.'
            );
        }

        $success = @pg_query($this->conn, $query);

        if (!$success) {
            throw new PostgresQueryException(
                pg_last_error()
            );
        } else {
            pg_query("SELECT pg_temp.UP()");
        }

        return $success;
    }
}
