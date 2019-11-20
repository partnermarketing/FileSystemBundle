<?php

namespace PartnerMarketing\FileSystemBundle\ServerFileSystem;

use DirectoryIterator;

class ServerFileSystem
{
    public static function getFilesInDirectory($dir)
    {
        $iterator = new DirectoryIterator($dir);
        $files = [];

        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            if (is_dir($file->getPathname())) {
                $files = array_merge($files, self::getFilesInDirectory($file->getPathname()));
            } else {
                $files[] = realpath($file->getPathname());
            }
        }

        sort($files);

        return $files;
    }

    public static function deleteFilesInDirectoryRecursively($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if (in_array($file->getFilename(), ['.', '..'])) {
                continue;
            }

            if (is_dir($file->getPathname())) {
                self::deleteFilesInDirectoryRecursively($file->getPathname());
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
    }
}
