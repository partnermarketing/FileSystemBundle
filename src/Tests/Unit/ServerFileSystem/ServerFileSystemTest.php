<?php

namespace PartnerMarketing\FileSystemBundle\Tests\Unit\ServerFileSystem;

use PartnerMarketing\FileSystemBundle\ServerFileSystem\ServerFileSystem;

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

        $this->assertCount(6, $files);
        $this->assertStringEndsWith('Unit/Adapter/LocalStorageTest.php', $files[0]);
        $this->assertStringEndsWith('Unit/Factory/FileSystemFactoryTest.php', $files[3]);
    }

    public function testDeleteFilesInDirectoryRecursively()
    {
        $dir = __DIR__ . '/deleteFilesInDirectoryRecursively/';

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        file_put_contents($dir . '1.txt', '1');
        file_put_contents($dir . '2.txt', '2');
        mkdir($dir . 'subdir');
        file_put_contents($dir . 'subdir/3.txt', '3');
        file_put_contents($dir . 'subdir/4.txt', '4');

        $filesBefore = ServerFileSystem::getFilesInDirectory($dir);
        $this->assertCount(4, $filesBefore);

        $files = ServerFileSystem::deleteFilesInDirectoryRecursively($dir);

        $filesAfter = ServerFileSystem::getFilesInDirectory($dir);
        $this->assertCount(0, $filesAfter);
    }
}
