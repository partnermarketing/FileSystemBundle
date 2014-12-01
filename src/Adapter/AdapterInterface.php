<?php

namespace Partnermarketing\FileSystemBundle\Adapter;

/**
 * Interface for all file system adapters.
 */
interface AdapterInterface
{
    /**
     * Reads the contents of the $path, and returns its content
     *
     * @param  string $path
     * @return string Content read from $path.
     */
    public function read($path);

    /**
     * Writes the content of the $source into the $path returns the URL
     *
     * @param  string $path
     * @param  string $source
     * @return string Path written to.
     */
    public function write($path, $source);

    /**
     * Writes the $content into the $path returns the URL
     *
     * @param  string $path
     * @param  string $content
     * @return string Path written to.
     */
    public function writeContent($path, $content);

    /**
     * Deletes the file $path
     *
     * @param  string  $path
     * @return boolean If successful.
     */
    public function delete($path);

    /**
     * Renames a file
     *
     * @param  string  $sourcePath
     * @param  string  $targetPath
     * @return boolean If successful.
     */
    public function rename($sourcePath, $targetPath);

    /**
     * Returns an array of files under given directory
     *
     * @param string $directory
     *
     * @returns array
     */
    public function getFiles($directory = '');

    /**
     * Copies all files under given source directory to given target directory
     *
     * @param string $sourceDir
     * @param string $targetDir
     *
     * @return boolean
     */
    public function copyFiles($sourceDir, $targetDir);

    /**
     * Checks if the $path exists
     *
     * @param string $path
     *
     * @return boolean
     */
    public function exists($path);

    /**
     * Checks if $path is a directory
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isDirectory($path);

    /**
     * Gets the Absoulte URL to an a file
     *
     * @param string $path
     *
     * @return string URL to the file
     */
    public function getURL($path);

    /**
     * Copy a file to the local temporary directory, and return the full path.
     *
     * @param  string $path
     * @return string Path to the copied temporary file.
     */
    public function copyToLocalTemporaryFile($path);
}
