<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;
use Exodus\File\Handler as FileHandler;
use Exodus\Database\Strategy\Factory as StrategyFactory;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * The Rollback Command Class
 *
 * This class is responsible for rolling back migrations when the 'rollback'
 * command is run.
 */
class RollbackCommand extends Command
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
        $this->setName('rollback');
        $this->setDescription('Rollback migrations');
        $this->addOption('last', 'l', InputOption::VALUE_REQUIRED, 'Rolls back the last n migrations.', null);
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
        // Get option to rollback the last "n" number of migrations
        $num_to_rollback = (int) $input->getOption('last');

        $this->strategy = StrategyFactory::getStrategy(
            $this->db_adapter,
            $this->migration_table
        );

        $migrations_to_rollback = $this->getMigrationsToRollback($num_to_rollback);

        // Stop execution if there is nothing to rollback
        if (empty($migrations_to_rollback)) {
            $output->writeln('<comment>No migrations to rollback.</comment>');
            exit;
        }

        try {

            $this->db_adapter->begin();

            $this->strategy->setUp();

            $this->rollbackMigrations($migrations_to_rollback);

            $this->strategy->tearDown();

            $this->db_adapter->commit();

            // Reverse the order back to normal
            $migrations_to_rollback = array_reverse($migrations_to_rollback);

            // Display files that were rolled back
            foreach ($migrations_to_rollback as $file) {
                $output->writeln("<comment>Rolled back:</comment> {$file}");
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
    protected function rollbackMigrations(array $migrations_to_rollback = [])
    {
        // Run each pending migration
        foreach ($migrations_to_rollback as $file_name) {

            // Read out the SQL contents
            $contents = $this->file_handler->fileGetContents(
                $this->migration_dir . DIRECTORY_SEPARATOR . $file_name
            );

            $this->strategy->runRollback($contents);
        }

        $this->strategy->removeMigrations($migrations_to_rollback);
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
    protected function getMigrationsToRollback(int $num_to_rollback = null)
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
