<?php

namespace Partnermarketing\FileSystemBundle\Factory;

use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;
use Partnermarketing\FileSystemBundle\Adapter\LocalStorage;
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
        $service = new AmazonClient(array(
            'credentials' => array(
                'key'    => $this->config['amazon_s3']['key'],
                'secret' => $this->config['amazon_s3']['secret']
            ),
            'region' => $this->config['amazon_s3']['region'],
            'version' => '2006-03-01'
        ));
        $fileSystem = new AmazonS3($service , $this->config['amazon_s3']['bucket'], 'public-read', $this->tmpDir);

        $acl = 'public-read';
        $allowedValues = [
            'private',
            'public-read',
            'public-read-write',
            'authenticated-read',
            'bucket-owner-read',
            'bucket-owner-full-control'
        ];
        if (!empty($this->config['amazon_s3']['acl'])) {
            if(!in_array($this->config['amazon_s3']['acl'], $allowedValues)){
                throw new \Exception('Invalid S3 acl value.');
            }
            $acl = $this->config['amazon_s3']['acl'];
        }
        $bucket = $this->config['amazon_s3']['bucket'];

        $fileSystem = new AmazonS3($service , $bucket, $acl, $this->tmpDir);


        return $fileSystem;
    }
}
