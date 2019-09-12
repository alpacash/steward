<?php

namespace App\Service\PhpConfig;

/**
 * Class AbstractPhpConfigService
 * @package App\Service\PhpConfig
 */
abstract class AbstractPhpConfigService
{
    const PHP_INI_FILE = "/usr/local/etc/php/7.1/php.ini";

    abstract public function getConfigItemString(): string;

    public function enable() {
        $disabledPattern = "/^;{$this->getConfigItemString()};*/im";
        $enabledPattern = "/^{$this->getConfigItemString()};*/im";

        $fileContent = preg_replace(
            $pattern,
            ";zend_extension=\"xdebug.so\";",
            $fileContent
        );

        \file_put_contents($fileName, $fileContent);
    }

    public function disable() {

    }
}
