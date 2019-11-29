<?php

namespace App\Exceptions;

use App\Contract\ServerContract;

class InvalidServerVersionException extends \Exception
{
    public function __construct(ServerContract $server, string $version)
    {
        parent::__construct("Invalid {$server->label()} version: {$version}. Please install it first.");
    }
}
