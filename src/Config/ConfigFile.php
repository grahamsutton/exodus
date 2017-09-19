<?php

namespace Exodus\Config;

use Symfony\Component\Yaml\Yaml;

/**
 * The Configuration File Class (exodus.yml)
 *
 * This class is a respresentation of the exodus.yml
 * configuration file.
 */
class ConfigFile
{
    /**
     * The path to the config file
     */
    protected $path;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Returns the path to the exodus.yml file.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Returns if this config file (exodus.yml) has been created
     * yet or not.
     *
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->getPath());
    }

    /**
     * Returns the location of the user's migration directory.
     *
     * Removes any trailing slashes in the process.
     *
     * @return string
     */
    public function getMigrationDir()
    {
        $contents = $this->getContents();

        if (!isset($contents['migration_dir'])) {
            throw new UndefinedConfigParamException(
                'The param "migration_dir" is not defined in your exodus.yml file.'
            );
        }

        return rtrim($contents['migration_dir'], DIRECTORY_SEPARATOR);
    }

    /**
     * Returns the contents of the exodus.yml file.
     *
     * @return array 
     */
    protected function getContents()
    {
        return Yaml::parse(file_get_contents($this->getPath()));
    }
}