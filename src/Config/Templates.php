<?php

namespace Exodus\Config;

use Exodus\Helpers\PathBuilder;

/**
 * The Templates Directory
 *
 * This class is a representation of the templates directory
 * located at exodus/templates.
 */
class Templates
{
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
     * Returns the path to the config file template.
     *
     * @return string
     */
    public function getConfigFilePath()
    {
        $path_builder = new PathBuilder();

        return $path_builder->build([$this->path, 'exodus.yml']);
    }

    /**
     * Returns the path to the SQL file template.
     *
     * @return string
     */
    public function getSQLTemplatePath()
    {
        $path_builder = new PathBuilder();

        return $path_builder->build([$this->path, 'postgres.sql']);
    }
}