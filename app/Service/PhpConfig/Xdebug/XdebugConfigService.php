<?php

namespace App\Service\PhpConfig\Xdebug;

use App\Service\PhpConfig\AbstractPhpConfigService;

/**
 * Class XdebugConfigService
 * @package App\Service\PhpConfig\Xdebug
 */
class XdebugConfigService extends AbstractPhpConfigService
{
    /**
     * @return string
     */
    public function getConfigItemString(): string
    {
        return 'zend_extension="xdebug.so"';
    }

    public function enable()
    {
        // TODO: Implement enable() method.
    }

    public function disable()
    {
        // TODO: Implement disable() method.
    }
}
