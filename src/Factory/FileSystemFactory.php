<?php

namespace Partnermarketing\FileSystemBundle\Factory;

use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;
use Partnermarketing\FileSystemBundle\Adapter\LocalStorage;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\S3Client as AmazonClient;
use Partnermarketing\FileSystemBundle\Adapter\AmazonS3;

/**
 * File system factory to deliver a file storage adapter.
 */
class FileSystemFactory
{
    private $defaultFileSystem;
    private $config;
    private $tmpDir;

    public function __construct($defaultFileSystem, $config, $tmpDir)
    {
        $this->defaultFileSystem = $defaultFileSystem;
        $this->config = $config;
        $this->tmpDir = $tmpDir;
    }

    /**
     * @return \Partnermarketing\FileSystemBundle\Adapter\AdapterInterface
     */
    public function build($adapterName = null)
    {
        if (is_null($adapterName)) {
            $adapterName = $this->defaultFileSystem;
        }

        switch ($adapterName) {
            case 'local_storage':
                return $this->buildLocalStorageFileSystem();
            case 'amazon_s3':
                return $this->buildAmazonS3FileSystem();
            default:
                throw new \Exception('The configuration for default_file_system needs to be set in the parameters.yml or the given adapter name did not match any existing file system');
        }
    }

    private function buildLocalStorageFileSystem()
    {
        $fileSystem = new LocalStorage(new SymfonyFileSystem(), $this->config['local_storage'], $this->tmpDir);

        return $fileSystem;
    }

    private function buildAmazonS3FileSystem()
    {
        $service = AmazonClient::factory(array(
            'key'    => $this->config['amazon_s3']['key'],
            'secret' => $this->config['amazon_s3']['secret'],
            'region' => $this->config['amazon_s3']['region']
        ));
        $acl = CannedAcl::PUBLIC_READ;
        if (!empty($this->config['amazon_s3']['acl'])) {
            $acl = $this->config['amazon_s3']['acl'];
        }
        $tmpDir = $this->tmpDir;
        $bucket = $this->config['amazon_s3']['bucket'];
        
        $fileSystem = new AmazonS3($service , $bucket, $acl, $tmpDir);

        return $fileSystem;
    }
}
