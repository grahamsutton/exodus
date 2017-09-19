<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Constructor
     *
     * @param \Exodus\Config\ConfigFile $config
     */
    public function __construct(ConfigFile $config_file)
    {
        parent::__construct();

        $this->config_file = $config_file;
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
        
    }
}
