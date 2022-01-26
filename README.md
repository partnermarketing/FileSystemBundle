FileSystemBundle
================

[![Build Status](https://travis-ci.org/partnermarketing/PartnermarketingFileSystemBundle.svg)](https://travis-ci.org/partnermarketing/PartnermarketingFileSystemBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingFileSystemBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingFileSystemBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingFileSystemBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/partnermarketing/PartnermarketingFileSystemBundle/?branch=master)
[![HHVM Status](http://hhvm.h4cc.de/badge/partnermarketing/file-system-bundle.svg)](http://hhvm.h4cc.de/package/partnermarketing/file-system-bundle)

FileSystemBundle is a file system component supporting different file storage adapters.


## Adapters:

### Local storage

This adapter was made to be used when you want to interact with your local file system. 

Config example:

```yml
    partnermarketing_file_system.default_file_system: local_storage
    partnermarketing_file_system.config:
        local_storage:
            path: /path/to/test/directory
            url: 'http://your-project-url.dev/test'

```


### Amazon S3

This adapter was made to be used when you want to interactive with Amazon S3 file system.


Config example:

```yml
    partnermarketing_file_system.default_file_system: amazon_s3
    partnermarketing_file_system.config:
        amazon_s3:
            key:    your-amazon-key
            secret: your-amazon-secret
            bucket: your-bucket-name
            region: eu-west-1
            acl:    public-read # Optional parameter.

```


## How to use

### Configuration 

First step is to pass the factory into where you need to use it.

```yml
# In your services.yml
    YourServiceName:
        class: Your\Namespace\Path\ServiceName
        arguments:
            fileSystemFactory:  @partnermarketing_file_system.factory
```

Then in your ServiceName.php file you can use the factory as you need.

```php

namespace Your\Namespace\Path;

use Partnermarketing\FileSystemBundle\Factory\FileSystemFactory;

class ServiceName
{
    private $fileSystem;
    
    public function __construct(FileSystemFactory $fileSystemFactory)
    {
        // This will build a fileSystem based on configs specified.
        $this->filesystem = $fileSystemFactory->build();
    }
}

```



### Read a file content

```php
$this->filesystem->read($varWithFilePath);
```

### Write content from a file to other

```php
// Writes the content of the $source into the $path returns the URL.
$url = $this->filesystem->write($path, $source);
```

### Write content into a file

```php
// Writes the $content into the $path returns the URL:
$url = $this->filesystem->writeContent($path, $content);
```

### Delete a file

```php
// Deletes the file $path:
$isDeleted = $this->filesystem->delete($path);
```

### Rename a file

```php
$isRenamed = $this->filesystem->rename($sourcePath, $targetPath);
```

### Get files from directory

```php
// Returns an array of files under given directory.
$filesArray = $this->filesystem->getFiles($directory = '');
```

### Copy files from one directory to another

```php
// Copies all files under given source directory to given target directory.
$filesArray = $this->filesystem->copyFiles($sourceDir, $targetDir);
```

### Check if a file exist

```php
$fileExists = $this->filesystem->exists($varWithFilePath);
```

### Check if path is a directory

```php
$isDirectory = $this->filesystem->isDirectory($varWithFilePath);
```

### Gets the Absolute URL to a file

```php
$absoluteFileUrl = $this->filesystem->getURL($path);
```

### Copy file to temporary directory

```php
// Copy a file to the local temporary directory, and return the full path.
$temporaryFilePath = $this->filesystem->copyToLocalTemporaryFile($path);
```






## How to contribute

You can add more adapters or improve the existing ones.

Create a pull request and please add tests if you fix a bug or added new functionality.

Report founded issues here:

https://github.com/partnermarketing/PartnermarketingFileSystemBundle/issues

