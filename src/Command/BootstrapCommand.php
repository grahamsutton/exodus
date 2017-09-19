<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;
use Exodus\Config\Templates;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapCommand extends Command
{
    /**
     * The configuration file (exodus.yml).
     *
     * @var \Exodus\Config\ConfigFile
     */
    protected $config_file;

    /**
     * The templates directory.
     *
     * @var \Exodus\Config\Templates
     */
    protected $templates;

    /**
     * Constructor
     *
     * @param \Exodus\Config\ConfigFile $config_file
     * @param \Exodus\Config\Templates  $templates
     */
    public function __construct(ConfigFile $config_file, Templates $templates)
    {
        parent::__construct();

        $this->config_file = $config_file;
        $this->templates   = $templates;
    }

    /**
     * Configure the details of the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('bootstrap');
        $this->setDescription('Initialize the exodus.yml configuration file.');
    }

    /**
     * Execute the command which creates the exodus.yml file 
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->config_file->exists()) {

            // Copy the config file template into the user's project
            copy($this->templates->getConfigFilePath(), $this->config_file->getPath());

            $output->writeln('<info>Created exodus.yml file.</info>');
        }
    }
}
