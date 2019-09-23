<?php

namespace App\Exceptions;

class ConfigFileMissingException extends ConfigFileException
{
    public function __construct(string $file, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("File does not exist: {$file}", $code, $previous);
    }
}
