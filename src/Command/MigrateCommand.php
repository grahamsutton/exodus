<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;
use Exodus\Database\Strategy\Factory as StrategyFactory;
use Exodus\Engine;

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
     * The Exodus engine used to perform migration operations.
     *
     * @var \Exodus\Engine
     */
    protected $engine;

    /**
     * Constructor
     *
     * @param \Exodus\Engine $engine
     */
    public function __construct(Engine $engine)
    {
        parent::__construct();

        $this->engine = $engine;
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
        // Create migrations table if it does not exist yet
        $this->engine->createMigrationTable();

        $migrations_to_run = $this->engine->getMigrationsToRun();

        // Kill command if no migrations to run
        if (empty($migrations_to_run)) {
            $output->writeln('<comment>No migrations to run.</comment>');
            exit;
        }

        try {

            $this->engine->runMigrations($migrations_to_run);

            // Display files that were migrated
            foreach ($migrations_to_run as $file) {
                $output->writeln("<info>Migrated:</info> {$file}");
            }

        } catch (\Exception $e) {

            $this->engine->onFailure();

            throw $e;
        }
    }
}
