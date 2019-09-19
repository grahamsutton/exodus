<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;
use Exodus\File\Handler as FileHandler;
use Exodus\Database\Strategy\Factory as StrategyFactory;
use Exodus\Engine;

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
     * The Exodus engine that powers all migration operations.
     *
     * @var \Exodus\Engine
     */
    protected $engine;

    /**
     * Constructor
     *
     * @param \Exodus\Config\ConfigFile $config
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
        $migrations_to_rollback = $this->engine->getMigrationsToRollback();

        // Stop execution if there is nothing to rollback
        if (empty($migrations_to_rollback)) {
            $output->writeln('<comment>No migrations to rollback.</comment>');
            exit;
        }

        try {

            $this->engine->rollbackMigrations($migrations_to_rollback);

            // Reverse the order back to normal for printing
            $migrations_to_rollback = array_reverse($migrations_to_rollback);

            // Display files that were rolled back
            foreach ($migrations_to_rollback as $file) {
                $output->writeln("<comment>Rolled back:</comment> {$file}");
            }

        } catch (\Exception $e) {

            $this->engine->onFailure();

            throw $e;
        }
    }
}
