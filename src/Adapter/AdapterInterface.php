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
     * Writes the content of the $source into the $path returns the same $path.
     *
     * @param  string $path
     * @param  string $source
     * @return string Path written to.
     */
    public function write($path, $source);

    /**
     * Writes the $content into the $path returns the same $path.
     *
     * @param  string $path
     * @param  string $content
     * @return string Path written to.
     */
    public function writeContent($path, $content);

    /**
     * Deletes the file at $path.
     *
     * @param  string  $path
     * @return boolean If successful.
     */
    public function delete($path);

    /**
     * Renames a file.
     *
     * @param  string  $sourcePath
     * @param  string  $targetPath
     * @return boolean If successful.
     */
    public function rename($sourcePath, $targetPath);

    /**
     * Returns an array of files under given directory. Recurses into any child directories.
     *
     * @param  string $directory
     * @return array
     */
    public function getFiles($directory = '');

    /**
     * Copies all files under given source directory to the given target directory.
     *
     * @param  string  $sourceDir
     * @param  string  $targetDir
     * @return boolean If successful.
     */
    public function copyFiles($sourceDir, $targetDir);

    /**
     * Checks if the $path exists
     *
     * @param  string $path
     * @return boolean
     */
    public function exists($path);

    /**
     * Checks if $path is a directory
     *
     * @param  string  $path
     * @return boolean
     */
    public function isDirectory($path);

    /**
     * Gets the absolute URL to the file at $path.
     *
     * @param  string $path
     * @return string URL to the file.
     */
    public function getURL($path);

    /**
     * Gets the size, in bytes, of the file at $path.
     *
     * @param  string $path
     * @return int    File size in bytes.
     */
    public function getFileSize($path);

    /**
     * Copy a file to the local temporary directory, and return the full path.
     *
     * @param  string $path
     * @return string Path to the copied temporary file.
     */
    public function copyToLocalTemporaryFile($path);
    
    /**
     * Gets the internal provider used for communication with the configured filesystem.
     * 
     * @return object The S3Client or SymfonyFileSystem object currently in-use
     */
    public function getService();
}
