<?php

namespace Exodus\Database\Strategy;

use Exodus\Database\Adapter;
use Exodus\Database\Adapter\Postgres as PostgresAdapter;
use Exodus\Database\Strategy\Postgres as PostgresStrategy;
use Exodus\Exception\InvalidDatabaseAdapterException;

/**
 * The Database Strategy Factory
 *
 * This class returns a database strategy based on the adapter
 * that was defined in the exodus.yml file.
 */
class Factory
{
    /**
     * Returns the strategy based on the provided adapter instance.
     *
     * The adapter instance is also provided to the factory as a dependency.
     *
     * @param Exodus\Database\Adapter $db_adapter
     * @param string $migrations_table
     *
     * @return Exodus\Database\Strategy
     */
    public static function getStrategy(Adapter $db_adapter, string $migrations_table)
    {
        if ($db_adapter instanceof PostgresAdapter) {
            return new PostgresStrategy($db_adapter, $migrations_table);
        }

        throw new InvalidDatabaseAdapterException(
            "The database adapter you provided in exodus.yml is invalid."
        );
    }
} 