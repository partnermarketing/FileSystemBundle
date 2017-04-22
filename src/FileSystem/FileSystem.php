<?php

namespace Partnermarketing\FileSystemBundle\FileSystem;

use Partnermarketing\FileSystemBundle\Adapter\AdapterInterface;
use Partnermarketing\FileSystemBundle\Exception\FileAlreadyExistsException;
use Partnermarketing\FileSystemBundle\Exception\FileDoesNotExistException;

class FileSystem implements AdapterInterface
{
    protected $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * {@inheritDoc}
     */
    public function read($path)
    {
        $content = $this->adapter->read($path);

        if (false === $content) {
            throw new \RuntimeException(sprintf('Could not read the "%s" content.', $path));
        }

        return $content;
    }

    /**
     * {@inheritDoc}
     */
    public function write($path, $source)
    {
        $url = $this->adapter->write($path, $source);

        if (empty($url)) {
            throw new \RuntimeException(sprintf('Could not write the "%s" key content.', $path));
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function writeContent($path, $content)
    {
        $url = $this->adapter->writeContent($path, $content);

        if (empty($url)) {
            throw new \RuntimeException(sprintf('Could not write the "%s" key content.', $path));
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function rename($sourcePath, $targetPath)
    {
        $this->assertHasFile($sourcePath);

        if ($this->exists($targetPath)) {
            throw new FileAlreadyExistsException($targetPath);
        }

        if (!$this->adapter->rename($sourcePath, $targetPath)) {
            throw new \RuntimeException(sprintf('Could not rename the "%s" key to "%s".', $sourcePath, $targetPath));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($path)
    {
        $this->assertHasFile($path);

        if ($this->adapter->delete($path)) {
            return true;
        }

        throw new \RuntimeException(sprintf('Could not remove the "%s" key.', $path));
    }

    /**
     * {@inheritDoc}
     */
    public function getFiles($directory = "")
    {
        return $this->adapter->getFiles($directory);
    }

    /**
     * {@inheritDoc}
     */
    public function copyFiles($sourceDir, $targetDir)
    {
        return $this->adapter->copyFiles($sourceDir, $targetDir);
    }

    /**
     * {@inheritDoc}
     */
    public function exists($path)
    {
        return $this->adapter->exists($path);
    }

    /**
     * {@inheritDoc}
     */
    public function isDirectory($path)
    {
        return $this->adapter->isDirectory($path);
    }

    /**
     * {@inheritDoc}
     */
    public function getURL($path)
    {
        return $this->adapter->getURL($path);
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiringURL($path, $expiresAt)
    {
        return $this->adapter->getExpiringURL($path, $expiresAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getFileSize($path)
    {
        return $this->adapter->getFileSize($path);
    }

    /**
     * {@inheritDoc}
     */
    public function copyToLocalTemporaryFile($path)
    {
        return $this->adapter->copyToLocalTemporaryFile($path);
    }
    
    /**
     * {@inheritDoc}
     */
    public function getService()
    {
        return $this->adapter->getService();
    }

    /**
     * Checks if a file exists, throws an error if it doesn't.
     *
     * @param $path
     * @throws FileDoesNotExistException
     */
    private function assertHasFile($path)
    {
        if (!$this->exists($path)) {
            throw new FileDoesNotExistException($path);
        }
    }
}
