<?php

namespace Exodus;

use Exodus\Database\Strategy;

/**
 * The Exodus Engine
 *
 * This class powers the flow of how exodus executes by utilizing
 * strategies and other common operations across all types of
 * databases.
 */
class Engine
{
    /**
     * The database strategy to execute operations against based on
     * the type of database adapter the user provided.
     *
     * @var \Exodus\Database\Strategy 
     */
    protected $strategy;

    /**
     * The configuration file, also known as exodus.yml.
     *
     * @var \Exodus\Config\ConfigFile
     */
    protected $config_file;

    /**
     * The templates object that contains the location to where
     * template files are stored and methods to access those templates.
     *
     * @var \Exodus\Config\Templates
     */
    protected $templates;

    /**
     * Wrapper class for file handling operations, such as file_get_contents,
     * scandir, etc.
     *
     * @var \Exodus\File\Handler
     */
    protected $file_handler;

    /**
     * Constructor
     *
     * @param \Exodus\Database\Strategy
     */
    public function __construct(array $dependencies = [])
    {
        $this->strategy     = $dependencies['strategy'];
        $this->config_file  = $dependencies['config_file'];
        $this->file_handler = $dependencies['file_handler'];
    }

    /**
     * Creates the migrations table if the table has not been created
     * yet. Returns true if the table was created, false if it was not.
     *
     * @return bool
     */
    public function createMigrationTable(): bool
    {
        if (!$this->strategy->isMigrationTableCreated()) {
            $this->strategy->createMigrationTable();
            return true;
        }

        return false;
    }

    /**
     * Runs the list of provided migrations.
     *
     * @param array $migrations
     *
     * @return void
     */
    public function runMigrations(array $migrations = []): void
    {
        $this->strategy->setUp();

        foreach ($migrations as $file_name) {

            // Read out the SQL contents
            $contents = $this->file_handler->fileGetContents(
                $this->config_file->getMigrationDir() . DIRECTORY_SEPARATOR . $file_name
            );

            $this->strategy->runMigration($contents);
        }

        // Inserts migrations into the migration table
        $this->strategy->addMigrations($migrations);

        $this->strategy->tearDown();
    }

    /**
     * Rolls back the list of provided migrations.
     *
     * @param array $migrations
     *
     * @return void
     */
    public function rollbackMigrations(array $migrations = []): void
    {
        $this->strategy->setUp();

        // Roll back each migration
        foreach ($migrations as $file_name) {

            // Read out the SQL contents
            $contents = $this->file_handler->fileGetContents(
                $this->config_file->getMigrationDir() . DIRECTORY_SEPARATOR . $file_name
            );

            $this->strategy->runRollback($contents);
        }

        $this->strategy->removeMigrations($migrations);

        $this->strategy->tearDown();
    }

    /**
     * Returns the list of migrations to run.
     *
     * We compare migrations from the migrations directory and compare it
     * against the migrations listed in the migrations table (if they are
     * in the database, it means they have been run). We then return the
     * array of files that are in the directory but not in the database.
     * These are the files that need to be run.
     *
     * @return array
     */
    public function getMigrationsToRun()
    {
        $migrations_ran    = $this->strategy->getMigrationsRan();
        $migrations_in_dir = $this->getMigrationsInDir();

        return array_values(array_diff($migrations_in_dir, $migrations_ran));
    }

    /**
     * Returns the list of migrations to rollback.
     *
     * We fetch a list of the migrations that are already in the migrations
     * table and begin reversing the order of the migrations so that they can
     * be rolled back in backwards order from latest to oldest.
     *
     * The $num_to_rollback parameter represents the number of migrations to
     * rollback. For example, if "3" was provided, we would rollback the last
     * three migrations. If null is provided, we rollback all migrations.
     *
     * If the provided number to rollback is greater than the total migrations
     * that have been run, then we will rollback all of them
     *
     * @param int $num_to_rollback
     *
     * @return array
     */
    public function getMigrationsToRollback(int $num_to_rollback = null)
    {
        $migrations_ran = $this->strategy->getMigrationsRan();

        if (!is_null($num_to_rollback)) {
            $total_migrations_ran = count($migrations_ran);

            $num_to_rollback = $num_to_rollback > $total_migrations_ran
                ? $total_migrations_ran 
                : $num_to_rollback;

            $migrations_ran = array_slice($migrations_ran, 0 - $num_to_rollback);
        }

        return array_reverse($migrations_ran);
    }

    /**
     * Returns the list of migration file names from the migrations directory.
     *
     * The scandir function returns other nodes in the directory like ".",
     * "..", and other sub directories, so in this case we exclude those nodes
     * from being returned.
     *
     * @return array
     */
    protected function getMigrationsInDir()
    {
        $migration_files = [];

        $migration_dir = $this->config_file->getMigrationDir();

        $dir_contents = $this->file_handler->fileExists($migration_dir)
            ? $this->file_handler->scanDir($migration_dir)
            : [];

        // Loop through each node in the migrations directory
        foreach ($dir_contents as $node) {

            // Verify the node is a file, if not, just skip it
            $is_file = $this->file_handler->isFile($migration_dir . DIRECTORY_SEPARATOR . $node);

            if ($is_file) {
                $migration_files[] = $node;
            }
        }

        return $migration_files;
    }

    /**
     * List of actions to perform when there is a failure with migrations.
     *
     * @return void
     */
    public function onFailure(): void
    {
        $this->strategy->rollback();
    }
}