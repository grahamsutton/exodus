<?php

namespace Exodus\Database\Adapter;

/**
 * The Database Adapter Factory
 *
 * This class is used to return the correct instance of a database
 * adapter used for connecting to and making transactions with a
 * database.
 */
class Factory
{
    /**
     * Returns the database adapter loaded with the configuration
     * params based on the type of adapter specified.
     *
     * @param array $db_params
     *
     * @return \Exodus\Database\Adapter
     */
    public function getDbAdapter($db_params)
    {
        switch ($db_params['adapter']) {

            // Postgres
            case Types::POSTGRES:
                return new Postgres($db_params);

            default:
                throw new InvalidDatabaseAdapter(
                    "Invalid database adapter name provided: $db_adapter"
                );
        }
    }
}