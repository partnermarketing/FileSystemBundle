<?php

namespace Partnermarketing\FileSystemBundle\Exception;

/**
 * Exception to be thrown when a file already exists
 */
class FileAlreadyExistsException extends \RuntimeException
{
    public function __construct($key, $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            sprintf('The file %s already exists and can not be overwritten.', $key),
            $code,
            $previous
        );
    }
}
