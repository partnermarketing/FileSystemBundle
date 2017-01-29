<?php

namespace Partnermarketing\FileSystemBundle\Exception;

/**
 * Exception to be thrown when a file does not exist.
 */
class FileDoesNotExistException extends \RuntimeException
{

    public function __construct($key, $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            sprintf('The file %s does not exist.', $key),
            $code,
            $previous
        );
    }
}
