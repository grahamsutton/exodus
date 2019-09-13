<?php

require_once __DIR__.'/vendor/autoload.php';

$config_file_path    = getcwd() . DIRECTORY_SEPARATOR . 'exodus.yml';
$templates_file_path =      '.' . DIRECTORY_SEPARATOR . 'templates';

$output       = new \Symfony\Component\Console\Output\ConsoleOutput();
$file_handler = new \Exodus\File\Handler();
$templates    = new \Exodus\Config\Templates($templates_file_path);

// Create the config file if it doesn't exist
if (!$file_handler->fileExists($config_file_path)) {

    // Copy the config file template into the user's project
    $file_handler->copy(
        $templates->getConfigFilePath(), 
        $config_file_path
    );

    $output->writeln('<info>Created exodus.yml file.</info>');
}

// Instantiate object for exodus.yml
$config_file = new \Exodus\Config\ConfigFile([
    'contents' => \Symfony\Component\Yaml\Yaml::parse(
        $file_handler->fileGetContents($config_file_path)
    ),
    'db_adapter_factory' => new \Exodus\Database\Adapter\Factory()
]);

$engine = new \Exodus\Engine([
    'strategy' => \Exodus\Database\Strategy\Factory::getStrategy(
        $config_file->getDbAdapter(),
        $config_file->getMigrationTable()
    ),
    'config_file'  => $config_file,
    'file_handler' => $file_handler
]);

// Register Commands
$application = new \Symfony\Component\Console\Application('Exodus Migrations CLI');

$application->add(new \Exodus\Command\MakeMigrationCommand(['config_file' => $config_file,'templates' => $templates]));
$application->add(new \Exodus\Command\MigrateCommand($engine));
$application->add(new \Exodus\Command\RollbackCommand($engine));