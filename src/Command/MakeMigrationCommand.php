<?php

namespace Exodus\Command;

use Exodus\Config\ConfigFile;
use Exodus\Config\Templates;
use Exodus\File\Extensions;
use Exodus\Exception\ConfigFileNotFoundException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * The Make Migration Command Class
 *
 * This class is responsible for generating the migration file script
 * when the make:migration script is run.
 */
class MakeMigrationCommand extends Command
{
    /**
     * The config file object (exodus.yml).
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
     * @param \Exodus\Config\ConfigFile $config
     * @param \Exodus\Config\Templates  $templates
     */
    public function __construct(array $dependencies = [])
    {
        parent::__construct();

        $this->config_file = $dependencies['config_file'];
        $this->templates   = $dependencies['templates'];
    }

    /**
     * Configure the details of the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('make:migration');
        $this->setDescription('Create a new migration script.');

        $this->addArgument('name', InputArgument::REQUIRED, 'The desired name of the migration file.');
    }

    /**
     * Execute the command which creates a new migration script.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file_path = $this->buildFilePath($input->getArgument('name'));

        // Create the migration directory if does not exist yet
        if (!file_exists($this->config_file->getMigrationDir())) {
            mkdir($this->config_file->getMigrationDir(), 0777, true);
        }

        // Copy the SQL template to the user's migration directory
        copy($this->templates->getSQLTemplatePath(), $file_path . Extensions::SQL);

        $output->writeln('<info>Created migration file.</info>');
    }

    /**
     * Builds the desired file name based on the specified migrations directory from
     * exodus.yml, the current timestamp in milliseconds, and the user provided name
     * for the file.
     *
     * @return string
     */
    protected function buildFilePath($file_name)
    {
        return implode([
            $this->config_file->getMigrationDir(),
            time() . '_' . $file_name
        ], DIRECTORY_SEPARATOR);
    }
}