<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Utility;

/**
 * Some wrappers around php standard library functions. These exist to make unit testing easier.
 */
class FileUtility
{
    /**
     * copy() wrapper
     *
     * @link https://www.php.net/manual/en/function.copy.php
     * @param string $source Source file
     * @param string $destination Destination file
     * @return bool
     */
    public function copy(string $source, string $destination): bool
    {
        return copy($source, $destination);
    }

    /**
     * file_get_contents() wrapper
     *
     * @link https://www.php.net/manual/en/function.file_get_contents.php
     * @param string $file The file
     * @return string|false
     */
    public function getContents(string $file): string|false
    {
        return file_get_contents($file);
    }

    /**
     * file_put_contents() wrapper
     *
     * @link https://www.php.net/manual/en/function.file_put_contents.php
     * @param string $file The file
     * @param string $data The data to write
     * @return int|false
     */
    public function putContents(string $file, string $data): int|false
    {
        return file_put_contents($file, $data);
    }

    /**
     * file_exists() wrapper
     *
     * @link https://www.php.net/manual/en/function.file_exists.php
     * @param string $file The file
     * @return bool
     */
    public function fileExists(string $file): bool
    {
        return file_exists($file);
    }

    /**
     * is_dir() wrapper
     *
     * @link https://www.php.net/manual/en/function.is_dir.php
     * @param string $directory The directory
     * @return bool
     */
    public function isDir(string $directory): bool
    {
        return is_dir($directory);
    }

    /**
     * is_writable() wrapper
     *
     * @link https://www.php.net/manual/en/function.is_writable.php
     * @param string $file The file
     * @return bool
     */
    public function isWritable(string $file): bool
    {
        return is_writable($file);
    }
}
