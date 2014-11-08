<?php

namespace Partnermarketing\FileSystemBundle\ServerFileSystem;

use DirectoryIterator;

class ServerFileSystem
{
    public static function getFilesInDirectory($dir)
    {
        $iterator = new DirectoryIterator($dir);
        $files = [];

        foreach ($iterator as $file) {
            if ($file->getFilename() === '.' || $file->getFilename() === '..') {
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
}
