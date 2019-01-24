<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;
use Exodus\File\Handler as FileHandler;
use Exodus\Database\Strategy\Factory as StrategyFactory;

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
     * The database strategy for executing commands to the database
     * based on the type of database defined in exodus.yml.
     *
     * @var \Exodus\Database\Strategy
     */
    protected $strategy;

    /**
     * The table that holds and stores migrations.
     *
     * @var string
     */
    protected $migration_table;

    /**
     * The directory where migrations should be created.
     *
     * @var string
     */
    protected $migration_dir;

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
        $this->migration_dir   = $config_file->getMigrationDir();
        $this->migration_table = $config_file->getMigrationTable();

        $this->file_handler = new FileHandler();
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
        $this->strategy = StrategyFactory::getStrategy(
            $this->db_adapter,
            $this->migration_table
        );

        // Create migrations table if not yet created.
        if (!$this->strategy->isMigrationTableCreated()) {

            $this->strategy->createMigrationTable();

            $output->writeln(
                "<info>Created migrations table under name '$this->migration_table'.</info>"
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

            $this->strategy->setUp();

            $this->runMigrations($migrations_to_run);

            $this->strategy->tearDown();

            $this->db_adapter->commit();

            // Display files that were migrated
            foreach ($migrations_to_run as $file) {
                $output->writeln("<info>Migrated:</info> {$file}");
            }

        } catch (\Exception $e) {

            $this->db_adapter->rollback();

            throw $e;
        }
    }

    /**
     * Runs the list of provided migrations and 
     *
     * @param array $migrations_to_run
     *
     * @return array
     */
    protected function runMigrations(array $migrations_to_run = [])
    {
        foreach ($migrations_to_run as $file_name) {

            // Read out the SQL contents
            $contents = $this->file_handler->fileGetContents(
                $this->migration_dir . DIRECTORY_SEPARATOR . $file_name
            );

            $this->strategy->runMigration($contents);
        }

        // Inserts migrations into the migration table
        $this->strategy->addMigrations($migrations_to_run);
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
        $migrations_ran    = $this->strategy->getMigrationsRan();
        $migrations_in_dir = $this->getMigrationsInDir();

        return array_diff($migrations_in_dir, $migrations_ran);
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

        $dir_contents = $this->file_handler->scanDir($migration_dir);

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
}
