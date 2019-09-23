<?php

namespace App;

class PhpServer
{
    protected static $configFile = "/usr/local/etc/php/%s/php.ini";

    /**
     * @return string
     */
    public function version()
    {
        preg_match("/^(PHP) ([\.\d]+)/i", shell_exec("php -v"), $matches);

        return last($matches);
    }

    /**
     * @return string
     */
    public function shortVersion()
    {
        list ($major, $minor) = explode(".", $this->version()) + ['7', '2'];

        return "{$major}.{$minor}";
    }

    /**
     * @return self
     */
    public function restart()
    {
        shell_exec("brew services restart php@{$this->shortVersion()}");

        return $this;
    }

    /**
     * @return string
     */
    public function iniFile()
    {
        return sprintf(self::$configFile, $this->shortVersion());
    }

    /**
     * @return \App\PhpConfig
     * @throws \Exception
     */
    public function config()
    {
        return new PhpConfig($this);
    }
}
