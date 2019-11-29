<?php

namespace App\Exceptions;

class ConfigFileCorruptException extends ConfigFileException
{
    public function __construct(string $file, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct("File seems corrupt: {$file}", $code, $previous);
    }
}
