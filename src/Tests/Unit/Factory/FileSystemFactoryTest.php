<?php

namespace Partnermarketing\FileSystemBundle\Tests\Functional\Factory;

use Partnermarketing\FileSystemBundle\Factory\FileSystemFactory;
use Partnermarketing\TestBundle\Tests\Base\BaseFunctionalTest;

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
        $this->assertInstanceOf('Partnermarketing\FileSystemBundle\Adapter\AdapterInterface',
            $this->factory->build());
    }

    public function testBuildLocalStorage()
    {
        $this->assertInstanceOf('Partnermarketing\FileSystemBundle\Adapter\LocalStorage',
            $this->factory->build('local_storage'));
    }

    public function testBuildAmazonS3()
    {
        $this->assertInstanceOf('Partnermarketing\FileSystemBundle\Adapter\AmazonS3',
            $this->factory->build('amazon_s3'));
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
        $this->assertInstanceOf('Partnermarketing\FileSystemBundle\Adapter\AmazonS3',$s3Adaptor);
    }
}
