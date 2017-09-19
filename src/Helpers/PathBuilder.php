<?php

namespace Exodus\Helpers;

/**
 * The Path Builder Class
 *
 * Helps build file paths in a consistent way.
 */
class PathBuilder
{
    /**
     * Builds and returns a file path based on the
     * provided nodes.
     *
     * @param array $nodes
     *
     * @return string
     */
    public function build(array $nodes)
    {
        return implode($nodes, DIRECTORY_SEPARATOR);
    }
}