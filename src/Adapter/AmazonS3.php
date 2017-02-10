<?php

namespace Partnermarketing\FileSystemBundle\Adapter;

use Aws\S3\S3Client as AmazonClient;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Mimetypes;

/**
 * Amazon specific file system adapter
 */
class AmazonS3 implements AdapterInterface
{
    private $service;
    private $bucket;
    private $localTmpDir;
    private $options;
    private $haveEnsuredBucketExists = false;

    /**
     * Constructor for AmazonS3 adapter
     *
     * @param \Aws\S3\S3Client $service
     * @param $bucket
     * @param string           $acl
     * @param array            $options
     */
    public function __construct(AmazonClient $service, $bucket, $localTmpDir, $acl = 'public-read', $options = array())
    {
        $this->service = $service;
        $this->bucket  = $bucket;
        $this->localTmpDir = $localTmpDir;
        $this->options = array_replace_recursive(
            array('create' => false, 'region' => 'eu-west-1', 'ACL' => $acl),
            $options
        );
    }

    /**
     * {@inheritDoc}
     */
    public function read($path)
    {
        // In this method only, $path can be absolute, for reading files from
        // outside of the file system directory, e.g. uploads, fixtures.
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        list($path, $bucket) = $this->pathOrUrlToPath($path);

        $this->ensureBucketExists();

        $response = $this->service->getObject(array(
            'Bucket' => $bucket,
            'Key' => $path
        ));

        $response['Body']->rewind();

        return (string) $response['Body'];
    }

    /**
     * {@inheritDoc}
     */
    public function write($path, $source)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        return $this->writeContent($path, EntityBody::factory(fopen($source, 'r')));
    }

    /**
     * {@inheritDoc}
     */
    public function writeContent($path, $content)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        $this->ensureBucketExists();

        $response = $this->service->putObject(array(
            'Bucket' => $bucket,
            'Key' => $path,
            'Body' => $content,
            'ACL' => $this->options['ACL'],
            'ContentType' => Mimetypes::getInstance()->fromFilename($path)
        ));

        $this->service->waitUntil('ObjectExists', array(
            'Bucket' => $bucket,
            'Key'    => $path
        ));

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function rename($sourcePath, $targetPath)
    {
        list($sourcePath, $sourceBucket) = $this->pathOrUrlToPath($sourcePath);
        list($targetPath, $targetBucket) = $this->pathOrUrlToPath($targetPath);

        $this->ensureBucketExists();

        $this->service->copyObject(array(
            'Bucket' => $targetBucket,
            'Key' => $targetPath,
            'CopySource' => urlencode($sourceBucket.'/'.$sourcePath),
            'ACL' => $this->options['ACL']
        ));

        $this->delete($sourcePath);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($path)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        $this->ensureBucketExists();

        return $this->service->deleteObject(array(
            'Bucket' => $bucket,
            'Key' => $path
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getFiles($directory = "")
    {
        list($directory, $bucket) = $this->pathOrUrlToPath($directory);

        $this->ensureBucketExists();

        $list = $this->service->getIterator('ListObjects', array(
            'Bucket' => $bucket, 'Prefix' => $directory
        ));

        $files = [];
        foreach ($list as $object) {
            $files[] = $object['Key'];
        }

        sort($files);

        return $files;
    }

    /**
     * {@inheritDoc}
     */
    public function copyFiles($sourceDir, $targetDir)
    {
        list($sourceDir, $sourceBucket) = $this->pathOrUrlToPath($sourceDir);
        list($targetDir, $targetBucket) = $this->pathOrUrlToPath($targetDir);

        /*
         * Add '/' character to the directories if necessary
         */
        $sourceDir = $sourceDir . (substr($sourceDir, -1) == '/' ? '' : '/');
        $targetDir = $targetDir . (substr($targetDir, -1) == '/' ? '' : '/');

        $this->ensureBucketExists();

        $files = $this->getFiles($sourceDir);

        $batch = array();
        for ($i = 0; $i < count($files); $i++) {
            $targetFile = str_replace($sourceDir, "", $files[$i]);
            $batch[] =  $this->service->getCommand('CopyObject', array(
                'Bucket'     => $targetBucket,
                'Key'        => "{$targetDir}{$targetFile}",
                'CopySource' => "{$sourceBucket}/{$files[$i]}",
            ));
        }

        try {
            $this->service->execute($batch);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Failed to copy files from %s to %s.', $sourceDir, $targetDir));
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function exists($path)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        return $this->service->doesObjectExist($bucket, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function isDirectory($path)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        if ($this->exists($path.'/')) {
            return true;
        }

        return false;
    }

    /**
     * Calls the bucket exist method, and returns its result.
     *
     * @return boolean
     */
    public function checkBucket()
    {
        return $this->ensureBucketExists();
    }

    /**
    * Ensures the specified bucket exists. If is does not, and create is true, it will try to create it.
    *
    * @return boolean
    */
    private function ensureBucketExists()
    {
        if ($this->haveEnsuredBucketExists) {
            return true;
        }

        if ($this->service->doesBucketExist($this->bucket)) {
            $this->haveEnsuredBucketExists = true;

            return true;
        }

        if (!$this->options['create']) {
            throw new \RuntimeException(sprintf(
                'The configured bucket "%s" does not exist.',
                $this->bucket
            ));
        }

        $response = $this->service->createBucket(
            array('Bucket' => $this->bucket, 'LocationConstraint' => $this->options['region'])
        );

        $this->service->waitUntil('BucketExists', array('Bucket' => $this->bucket));

        if (!$response['Location']) {
            throw new \RuntimeException(sprintf(
                'Failed to create the configured bucket "%s".',
                $this->bucket
            ));
        }

        $this->haveEnsuredBucketExists = true;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getURL($path)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        return $this->service->getObjectUrl($bucket, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function getFileSize($path)
    {
        list($path, $bucket) = $this->pathOrUrlToPath($path);

        return (int) $this->service->headObject([
            'Bucket' => $bucket,
            'Key' => $path,
        ])->get('ContentLength');
    }

    /**
     * {@inheritDoc}
     */
    public function copyToLocalTemporaryFile($path)
    {
        $content = $this->read($path);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $target = tempnam($this->localTmpDir, null) . '.' . $extension;

        file_put_contents($target, $content);

        return $target;
    }

    /**
     * {@inheritDoc}
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Returns an s3 location in normalised format, plus parses the bucket name
     * from the URL if a URL is used.
     *
     * @param  string $input
     * @return array  Contains two values:
     *                Index 0: the path to the file in S3.
     *                Index 1: the S3 bucket name.
     */
    private function pathOrUrlToPath($input)
    {
        $bucket = $this->bucket;

        if (empty($input)) {
            return ['', $bucket];
        }
        if (strpos($input, 'http://') === 0 || strpos($input, 'https://') === 0) {
            $path = parse_url($input, PHP_URL_PATH);

            /**
             *  Try to detect the bucket name from the hostname
             */
            $host = parse_url($input, PHP_URL_HOST);
            if (preg_match('/.s3[-\.a-z0-9]*\.amazonaws\.com$/', $host)) {
                $bucket = substr($host, 0, strpos($host, '.s3'));
            } else {
                $bucket = $host;
            }
        } else {
            $path = $input;
        }

        return [ltrim($path, '/'), $bucket];
    }
}
