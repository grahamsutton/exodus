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
     * The path to the config file.
     *
     * @var string
     */
    protected $path;

    /**
     * The list of depdendencies for this class.
     *
     * @var array
     */
    protected $dependencies;

    /**
     * The contents of the exodus.yml file.
     *
     * @var array
     */
    protected $contents;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($path, $db_adapter_factory)
    {
        $this->path               = $path;
        $this->db_adapter_factory = $db_adapter_factory;
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
     * Returns the name of the migration table specified to be used for migrations.
     *
     * @return string
     */
    public function getMigrationTable()
    {
        $contents = $this->getContents();

        if (!isset($contents['migration_table'])) {
            throw new UndefinedConfigParamException(
                'The param "migration_table" is not defined in your exodus.yml file.'
            );
        }

        return trim($contents['migration_table']);
    }

    /**
     * Creates and returns a database adapter as specified in the exodus.yml
     * file.
     *
     * @return Exodus\Database\Adapter
     */
    public function getDbAdapter()
    {
        $contents = $this->getContents();

        if (!isset($contents['db']['adapter'])) {
            throw new UndefinedConfigParamException(
                'The param ' . implode(':', ['db', 'adapter']) . ' is not defined ' .
                'in your exodus.yml file.'
            );
        }

        return $this->db_adapter_factory->getDbAdapter($contents['db']);
    }

    /**
     * Returns the contents of the exodus.yml file.
     *
     * Contents are lazy loaded.
     *
     * @return array 
     */
    protected function getContents()
    {
        if (!$this->contents) {
            $this->contents = Yaml::parse(file_get_contents($this->getPath()));
        }

        return $this->contents;
    }
}