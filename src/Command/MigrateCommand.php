<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The Migrate Command Class
 *
 * This class is responsible for running migrations when the 'migrate'
 * command is run.
 */
class MigrateCommand extends Command
{
    /**
     * The config file object (exodus.yml).
     *
     * @var \Exodus\Config\ConfigFile
     */
    protected $config_file;

    /**
     * The database adapter used to connect to the database
     * instance.
     *
     * @var \Exodus\Database\Adapter
     */
    protected $db_adapter;

    /**
     * The table that holds and stores migrations.
     *
     * @var string
     */
    protected $migration_table;

    /**
     * Constructor
     *
     * @param \Exodus\Config\ConfigFile $config
     */
    public function __construct(ConfigFile $config_file)
    {
        parent::__construct();

        $this->config_file     = $config_file;
        $this->db_adapter      = $config_file->getDbAdapter();
        $this->migration_table = $config_file->getMigrationTable();
    }

    /**
     * Configure the details of the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('migrate');
        $this->setDescription('Run pending migrations');
    }

    /**
     * Execute the command which runs all migrations by analyzing the current directory and
     * running any of the migration that are currently missing.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $this->db_adapter->connect();
        // $this->db_adapter->rollback();
        // return;

        $migration_dir   = $this->config_file->getMigrationDir();
        $migration_table = $this->config_file->getMigrationTable();

        // Create migrations table if not yet created.
        if (!$this->isMigrationTableCreated()) {

            $this->createMigrationTable();

            $output->writeln(
                "<info>Created migrations table under name '$migration_table'.</info>"
            );
        }

        $migrations_to_run = $this->getMigrationsToRun();

        // Kill command if no migrations to run
        if (empty($migrations_to_run)) {

            $output->writeln('<comment>No migrations to run.</comment>');

            exit;
        }

        try {

            $this->db_adapter->begin();

            $migrated_files = [];

            // Run each pending migration
            foreach ($migrations_to_run as $migration) {

                // Get the full path to the migration
                $full_path = $migration_dir . DIRECTORY_SEPARATOR . $migration;

                // Read out the SQL contents
                $contents = file_get_contents($full_path);

                // Attempt to execute the for the current migration
                $test = $this->db_adapter->execute($contents);

                $migrated_files[] = $migration;
            }

            // Add the files that were migrated to the migrations table.
            $this->markAsMigrated($migrated_files);

            // $this->db_adapter->commit();

            // Display files that were migrated
            foreach ($migrated_files as $file) {
                $output->writeln("<info>Migrated:</info> {$file}");
            }

        } catch (\Exception $e) {

            $this->db_adapter->rollback();

            throw $e;
        }
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
    protected function getMigrationsToRun()
    {
        $migrations_ran    = $this->getMigrationsRan();
        $migrations_in_dir = $this->getMigrationsInDir();

        return array_diff($migrations_in_dir, $migrations_ran);
    }

    /**
     * Creates the migration table.
     *
     * @return void
     */
    protected function createMigrationTable()
    {
        $migration_table = $this->config_file->getMigrationTable();

        $resource = $this->db_adapter->query("
            CREATE TABLE $this->migration_table (
                file VARCHAR PRIMARY KEY,
                ran_at TIMESTAMP DEFAULT NOW()
            );
        ");
    }

    /**
     * Returns whether the migration table is already created.
     *
     * @return bool
     */
    protected function isMigrationTableCreated()
    {
        // TODO: Move query to a query factory based on db_adapter
        $resource = $this->db_adapter->query("
            SELECT relname 
            FROM pg_class 
            WHERE relname = '$this->migration_table';
        ");

        // TODO: Needs to be abstracted from this class
        $result = pg_fetch_row($resource);

        return $result[0];
    }

    /**
     * Return the list of migrations that have already been run.
     *
     * Migrations that have already been run will appear in the
     * migrations table.
     *
     * @return array
     */
    protected function getMigrationsRan()
    {
        // TODO: Move query to a query factory based on db_adapter
        $resource = $this->db_adapter->query(
            "SELECT file FROM $this->migration_table"
        );

        // TODO: Needs to be abstracted from this class
        $results = pg_fetch_all($resource) ?: [];

        // Flatten the array structure to a single dimension
        $migrations_ran = array_map(function ($result) {
            return $result['file'];
        }, $results);

        return $migrations_ran;
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

        $dir_contents = scandir($migration_dir);

        // Loop through each node in the migrations directory
        foreach ($dir_contents as $node) {

            // Verify the node is a file, if not, just skip it
            $is_file = is_file($migration_dir . DIRECTORY_SEPARATOR . $node);

            if ($is_file) {
                $migration_files[] = $node;
            }
        }

        return $migration_files;
    }

    /**
     * Adds migrated files to the migration table so that they won't be re-run
     * again.
     *
     * @param array $migrated_files
     *
     * @return void
     */
    protected function markAsMigrated($migrated_files = [])
    {
        foreach ($migrated_files as $migrated_file) {
            $this->db_adapter->execute("
                INSERT INTO $this->migration_table (file) VALUES ('$migrated_file')
            ");
        }
    }
}
