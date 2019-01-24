<?php

namespace Exodus\File;

/**
 * The File Handler Class
 *
 * This class serves as a wrapper to native PHP file handling
 * methods like "is_file", "is_dir", "file_get_contents", and
 * more. This class exists to make it easier to mock file
 * handling actions in unit tests.
 */
class Handler
{
    /**
     * Delegates call to PHP's native file_get_contents() function.
     *
     * @param string $file_path
     *
     * @return mixed
     */
    public function fileGetContents(string $file_path)
    {
        return file_get_contents($file_path);
    }

    /**
     * Delegates call to PHP's native scandir() function.
     *
     * @param string $dir_path
     *
     * @return mixed
     */
    public function scanDir($dir_path)
    {
        return scandir($dir_path);
    }

    /**
     * Delegates call to PHP's native is_file() function.
     *
     * @param string $file_path
     *
     * @return bool
     */
    public function isFile(string $file_path)
    {
        return is_file($file_path);
    }
}