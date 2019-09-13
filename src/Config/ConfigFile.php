<?php

namespace Exodus\Config;

use Exodus\Exception\UndefinedConfigParamException;

/**
 * The Configuration File Class (exodus.yml)
 *
 * This class is a respresentation of the exodus.yml
 * configuration file.
 */
class ConfigFile
{
    /**
     * The contents of the exodus.yml file.
     *
     * @var array
     */
    protected $contents;

    /**
     * The database adapter factory. Used for retrieving the database
     * adapter specified by the user.
     *
     * @var \Exodus\Database\Adapter\Factory
     */
    protected $db_adapter_factory;

    /**
     * Constructor
     *
     * @param string $contents
     */
    public function __construct(array $dependencies = [])
    {
        $this->contents           = $dependencies['contents'];
        $this->db_adapter_factory = $dependencies['db_adapter_factory'];
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
        if (!isset($this->contents['migration_dir'])) {
            throw new UndefinedConfigParamException(
                'The param "migration_dir" is not defined in your exodus.yml file.'
            );
        }

        return rtrim($this->contents['migration_dir'], DIRECTORY_SEPARATOR);
    }

    /**
     * Returns the name of the migration table specified to be used for migrations.
     *
     * @return string
     */
    public function getMigrationTable()
    {
        if (!isset($this->contents['migration_table'])) {
            throw new UndefinedConfigParamException(
                'The param "migration_table" is not defined in your exodus.yml file.'
            );
        }

        return trim($this->contents['migration_table']);
    }

    /**
     * Creates and returns a database adapter as specified in the exodus.yml
     * file.
     *
     * @return Exodus\Database\Adapter
     */
    public function getDbAdapter()
    {
        if (empty($this->contents['db']['adapter'])) {
            throw new UndefinedConfigParamException(
                'The param db:adapter is not defined in your exodus.yml file.'
            );
        }

        return $this->db_adapter_factory->getDbAdapter($this->contents['db']);
    }
}
