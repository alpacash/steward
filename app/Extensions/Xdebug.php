<?php

namespace App\Extensions;

use App\PhpExtension;
use App\PhpServer;

class Xdebug extends PhpExtension
{
    public function __construct(
        PhpServer $server,
        string $suffix = ".so"
    ) {
        parent::__construct($server, "xdebug", self::ZEND_EXTENSION, $suffix);
    }
}
