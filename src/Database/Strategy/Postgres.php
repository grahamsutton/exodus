<?php

namespace Exodus\Database\Strategy;

use Exodus\Database\Strategy;
use Exodus\Database\Adapter\Postgres as PostgresAdapter;

/**
 * The Postgres Strategy Class
 *
 * Defines how Exodus operations are performed when using a Postgres 
 * database.
 */
class Postgres implements Strategy
{
    /**
     * The Postgres Database Adapter.
     *
     * @var Exodus\Database\Adapter\Postgres
     */
    protected $db_adapter;

    /**
     * The name of the migrations table defined in the user's exodus.yml.
     *
     * @var string
     */
    protected $migration_table;

    /**
     * Constructor
     *
     * @param Exodus\Database\Adapter\Postgres $db_adapter
     * @param string $migration_table
     *
     */
    public function __construct(PostgresAdapter $db_adapter, $migration_table)
    {
        $this->db_adapter      = $db_adapter;
        $this->migration_table = $migration_table;
    }

    /**
     * Creates the migrations table using Postgres-specific syntax.
     *
     * @return void
     */
    public function createMigrationTable(): void
    {
        $this->db_adapter->query("
            CREATE TABLE $this->migration_table (
                file VARCHAR PRIMARY KEY,
                ran_at TIMESTAMP DEFAULT NOW(),
                batch INT NOT NULL
            );
        ");
    }

    /**
     * Returns if the table provided has already been created.
     *
     * @return bool
     */
    public function isMigrationTableCreated(): bool
    {
        $resource = $this->db_adapter->query("
            SELECT relname 
            FROM pg_class 
            WHERE relname = '$this->migration_table';
        ");

        $result = pg_fetch_row($resource);

        return !empty($result[0]);
    }

    /**
     * Returns the list of migrations that have already been executed and
     * are in the database migrations table.
     *
     * This method defaults to returning the last batch of migrations that
     * were ran. This allows the developer to only undo the last changes
     * that were committed to the database and avoid accidentally wiping
     * the entire database clean on rollbacks.
     *
     * @return array
     */
    public function getMigrationsRan(): array
    {
        try {

            $this->db_adapter->begin();

            $latest_batch = $this->getLatestBatchNumber();

            $resource = $this->db_adapter->execute("
                SELECT file
                FROM $this->migration_table
                WHERE batch = $latest_batch
                ORDER BY ran_at ASC
            ");

            $this->db_adapter->commit();

        } catch (\Exception $e) {

            $this->db_adapter->rollback();

            throw $e;
        }

        $results = pg_fetch_all($resource) ?: [];

        // Flatten the array structure to a single dimension
        $migrations_ran = array_map(function ($result) {
            return $result['file'];
        }, $results);

        return $migrations_ran;
    }

    /**
     * Inserts the list of migration files into the user-defined migrations table,
     * essentially marking them as "ran".
     *
     * @param array $migrated_files
     * @param int   $batch_number
     *
     * @return void
     */
    public function addMigrations(array $migrated_files = [], int $batch_number): void
    {
        foreach ($migrated_files as $migrated_file) {
            $this->db_adapter->execute("
                INSERT INTO $this->migration_table (file, batch) VALUES ('$migrated_file', $batch_number)
            ");
        }
    }

    /**
     * Deletes the list of migrations from the migrations table. This will be performed
     * during a rollback so that they can be run again.
     *
     * @param array $migrated_files
     * @param int   $batch_number
     *
     * @return void
     */
    public function removeMigrations(array $migrated_files = [], int $batch_number): void
    {
        foreach ($migrated_files as $migrated_file) {
            $this->db_adapter->execute("
                DELETE FROM $this->migration_table WHERE file = '$migrated_file' AND batch = $batch_number
            ");
        }
    }

    /**
     * Runs a single migration. Accepts the SQL contents from a file as the
     * parameter.
     *
     * @param mixed $sql
     *
     * @return void
     */
    public function runMigration($sql): void
    {
        // Creates defined UP() and DOWN() SQL functions
        $this->db_adapter->execute($sql);

        // Executes the UP() function
        $this->db_adapter->execute("SELECT exodus_tmp.UP()");
    }

    /**
     * Runs a single rollback. Accepts the SQL contents from a file as the
     * parameter.
     *
     * @param mixed $sql
     *
     * @return void
     */
    public function runRollback($sql): void
    {
        // Creates the defined UP() and DOWN() SQL functions
        $this->db_adapter->execute($sql);

        // Executes the DOWN() function
        $this->db_adapter->execute("SELECT exodus_tmp.DOWN()");
    }

    /**
     * Sets up a temporary schema to initiate the UP() and DOWN() procedures for
     * Postgres. This schema should be destroyed after migrations have run.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->db_adapter->begin();

        $this->db_adapter->execute("
            CREATE SCHEMA exodus_tmp;
        ");
    }

    /**
     * Destroys the temporary schema and commits all transactions.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->db_adapter->execute("
            DROP SCHEMA exodus_tmp CASCADE;
        ");

        $this->db_adapter->commit();
    }

    /**
     * Rollback any operations or transactions performed in case of failure.
     *
     * @return void
     */
    public function rollback(): void
    {
        $this->db_adapter->rollback();
    }

    /**
     * Return the highest value in the "batch" column. The highest value in
     * the "batch" signifies the latest run batch.
     *
     * The "batch" column is located in the migrations table.
     *
     * Batches are used to perform rollbacks that only undo the last execution
     * and not the entire database.
     *
     * @return int
     */
    public function getLatestBatchNumber(): int
    {
        $resource = $this->db_adapter->execute(
            "SELECT MAX(batch) FROM $this->migration_table"
        );

        $batch_number = pg_fetch_row($resource);

        return !is_null($batch_number[0]) ? $batch_number[0] : 0;
    }
}