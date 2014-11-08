<?php

namespace Partnermarketing\FileSystemBundle\Tests\Unit\ServerFileSystem;

use Partnermarketing\FileSystemBundle\ServerFileSystem\ServerFileSystem;

class ServerFileSystemTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFilesInDirectory()
    {
        $files = ServerFileSystem::getFilesInDirectory(__DIR__);

        $this->assertCount(1, $files);
    }

    public function testGetFilesInDirectoryIsRecursiveAndAlphabetical()
    {
        $files = ServerFileSystem::getFilesInDirectory(__DIR__  . '/../');

        $this->assertCount(2, $files);
        $this->assertStringEndsWith('Unit/Factory/FileSystemFactoryTest.php', $files[0]);
        $this->assertStringEndsWith('Unit/ServerFileSystem/ServerFileSystemTest.php', $files[1]);
    }
}
