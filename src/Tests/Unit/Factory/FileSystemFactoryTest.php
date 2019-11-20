<?php

namespace PartnerMarketing\FileSystemBundle\Tests\Functional\Factory;

use PartnerMarketing\FileSystemBundle\Factory\FileSystemFactory;
use PartnerMarketing\TestBundle\Tests\Base\BaseFunctionalTest;

class FileSystemFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->config = [
            'local_storage' => ['path' => '', 'url' => ''],
            'amazon_s3' => ['key' => '', 'secret' => '', 'region' => '', 'bucket' => ''],
        ];
        $this->factory = new FileSystemFactory('local_storage', $this->config, '/tmp');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage the given adapter name did not match any existing file system
     */
    public function testThrowsAnErrorForInvalidAdapter()
    {
        $this->factory->build('xyz');
    }

    public function testBuildDefault()
    {
        $this->assertInstanceOf(
            'PartnerMarketing\FileSystemBundle\Adapter\AdapterInterface',
            $this->factory->build()
        );
    }

    public function testBuildLocalStorage()
    {
        $this->assertInstanceOf(
            'PartnerMarketing\FileSystemBundle\Adapter\LocalStorage',
            $this->factory->build('local_storage')
        );
    }

    public function testBuildAmazonS3()
    {
        $this->assertInstanceOf(
            'PartnerMarketing\FileSystemBundle\Adapter\AmazonS3',
            $this->factory->build('amazon_s3')
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid S3 acl value.
     */
    public function testAmazonConfigAclInvalid()
    {
        $this->config = [
            'local_storage' => ['path' => '', 'url' => ''],
            'amazon_s3' => ['key' => '', 'secret' => '', 'region' => '', 'bucket' => '', 'acl' => 'read'],
        ];
        $this->factory = new FileSystemFactory('amazon_s3', $this->config, '/tmp');
        $this->factory->build('amazon_s3');
    }

    /**
     * Should
     */
    public function testAmazonConfigAclValid()
    {
        $this->config = [
            'local_storage' => ['path' => '', 'url' => ''],
            'amazon_s3' => ['key' => '', 'secret' => '', 'region' => '', 'bucket' => '', 'acl' => 'public-read'],
        ];
        $this->factory = new FileSystemFactory('amazon_s3', $this->config, '/tmp');

        $s3Adaptor = $this->factory->build('amazon_s3');
        $this->assertInstanceOf('PartnerMarketing\FileSystemBundle\Adapter\AmazonS3', $s3Adaptor);
    }

    public function testDefaultsTempDirCorrectly()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped('HHVM does not support the moving of the configured temp directory as PHP does.');
        }

        //Force PHP to use this directory as the system temp directory.
        putenv('TMPDIR='.__DIR__.'/');

        $this->config = [
            'local_storage' => ['path' => '', 'url' => 'http://localhost/tmp'],
            'amazon_s3' => ['key' => '', 'secret' => '', 'region' => '', 'bucket' => ''],
        ];
        $this->factory = new FileSystemFactory('amazon_s3', $this->config);
        $adapter = $this->factory->build('local_storage');

        $initFile = __DIR__.'/lorem.txt';
        $adapter->writeContent($initFile, 'Lorem Ipsum');
        $tmpFile = $adapter->copyToLocalTemporaryFile($initFile);

        $this->assertCount(4, $adapter->getFiles(__DIR__));
        $this->assertTrue($adapter->exists($tmpFile));

        $adapter->delete($initFile);
        $adapter->delete($tmpFile);
        $adapter->delete(substr($tmpFile, 0, (strrpos($tmpFile, "."))));
    }
}
