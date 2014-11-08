<?php

namespace Partnermarketing\FileSystemBundle\Adapter;

use Partnermarketing\FileSystemBundle\Exception\FileDoesNotExistException;

/**
 * LocalStorage specific file system adapter
 */
class LocalStorage implements AdapterInterface
{
    protected $service;
    protected $absolutePath;
    protected $webUrl;
    protected $localTmpDir;

    /**
     * Constructor for LocalStorage adapter
     */
    public function __construct($service, $parameter, $localTmpDir)
    {
        $this->service = $service;

        $absolutePathNormalised = '/' . trim($parameter['path'], '/');
        if (is_dir($absolutePathNormalised) && realpath($absolutePathNormalised)) {
            $this->absolutePath = realpath($absolutePathNormalised);
        } else {
            throw new FileDoesNotExistException($absolutePathNormalised);
        }

        $this->webUrl = trim($parameter['url'], '/');

        $this->localTmpDir = $localTmpDir;
    }

    /**
     * {@inheritDoc}
     */
    public function read($path)
    {
        // In this method only, $path can be absolute, for reading files from
        // outside of the file system directory, e.g. uploads, fixtures.
        if (file_exists($path)) {
            $fullPath = $path;
        } else {
            $path = $this->pathOrUrlToPath($path);
            $fullPath = $this->absolutePath . '/' . $path;
        }

        return (string) file_get_contents($fullPath);
    }

    /**
     * {@inheritDoc}
     */
    public function write($path, $source)
    {
        $path = $this->pathOrUrlToPath($path);

        return $this->writeContent($path, $this->read($source));
    }

    /**
     * {@inheritDoc}
     */
    public function writeContent($path, $content)
    {
        $path = $this->pathOrUrlToPath($path);

        $this->service->dumpFile($this->absolutePath.'/'.$path, $content);

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function rename($sourcePath, $targetPath)
    {
        $sourcePath = $this->pathOrUrlToPath($sourcePath);
        $targetPath = $this->pathOrUrlToPath($targetPath);

        $this->service->rename($this->absolutePath.'/'.$sourcePath, $this->absolutePath.'/'.$targetPath);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($path)
    {
        $path = $this->pathOrUrlToPath($path);

        $resp = $this->service->remove($this->absolutePath.'/'.$path);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getFiles($directory = "")
    {
        $directory = $this->pathOrUrlToPath($directory);

        $array = [];

        if ($handle = opendir($this->absolutePath.'/'.$directory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (!empty($directory)) {
                        $array[] = $directory.'/'.$entry;
                    } else {
                        $array[] = $entry;
                    }
                }
            }
            closedir($handle);
        }

        return $array;
    }

    public function mkdir($dir)
    {
        $dir = $this->pathOrUrlToPath($dir);

        $this->service->mkdir($this->absolutePath.'/'.$dir);
    }

    public function copy($originFile, $targetFile)
    {
        $originFile = $this->pathOrUrlToPath($originFile);
        $targetFile = $this->pathOrUrlToPath($targetFile);

        $this->service->copy($this->absolutePath.'/'.$originFile, $this->absolutePath.'/'.$targetFile);
    }

    /**
     * {@inheritDoc}
     */
    public function copyFiles($sourceDir, $targetDir)
    {
        $sourceDir = $this->pathOrUrlToPath($sourceDir);
        $targetDir = $this->pathOrUrlToPath($targetDir);

        if ($sourceDir === $targetDir) {
            throw new \RuntimeException(sprintf('Failed to copy files from %s to %s.', $sourceDir, $targetDir));
        }

        // loop trough all files within the folder
        if ($handle = opendir($this->absolutePath.'/'.$sourceDir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (!empty($sourceDir)) {
                        $this->copy($sourceDir.'/'.$entry, $targetDir.'/'.$entry);
                    } else {
                        $this->copy($entry, $targetDir.'/'.$entry);
                    }
                }
            }
            closedir($handle);
        }

    }

    /**
     * {@inheritDoc}
     */
    public function exists($path)
    {
        $path = $this->pathOrUrlToPath($path);

        return $this->service->exists($this->absolutePath.'/'.$path);
    }

    /**
     * {@inheritDoc}
     */
    public function isDirectory($path)
    {
        $path = $this->pathOrUrlToPath($path);

        if (is_dir($this->absolutePath.'/'.$path) && !is_link($this->absolutePath.'/'.$path)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getURL($path)
    {
        $path = $this->pathOrUrlToPath($path);

        return $this->webUrl.'/'.$path;
    }

    /**
     * {@inheritDoc}
     */
    public function copyToLocalTemporaryFile($path)
    {
        $content = $this->read($path);
        $target = tempnam($this->localTmpDir, null);

        file_put_contents($target, $content);

        return $target;
    }

    private function pathOrUrlToPath($it)
    {
        if (empty($it)) {
            return '';
        }

        if (strpos($it, $this->webUrl) === 0) {
            $it = str_replace($this->webUrl, '', $it);
        } elseif (strpos($it, 'http://') === 0 || strpos($it, 'https://') === 0) {
            $it = parse_url($it, PHP_URL_PATH);
        }

        return trim($it, '/');
    }
}
