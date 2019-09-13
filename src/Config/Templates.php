<?php

namespace Exodus\Config;

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
        return $this->path . DIRECTORY_SEPARATOR . 'exodus.yml';
    }

    /**
     * Returns the path to the SQL file template.
     *
     * @return string
     */
    public function getSQLTemplatePath()
    {
        return $this->path . DIRECTORY_SEPARATOR . 'postgres.sql';
    }
}