<?php

namespace App;

class PhpServer
{
    const PHP_VERSIONS = ['7.0', '7.1', '7.2', '7.3'];

    /**
     * @var string
     */
    protected static $configFile = "/usr/local/etc/php/%s/php.ini";

    /**
     * @return string
     */
    public function version()
    {
        preg_match("/^(PHP) ([\.\d]+)/i", shell_exec('php -v'), $matches);

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
        Shell::cmd("brew services restart php@{$this->shortVersion()}");

        return $this;
    }

    /**
     * @return self
     */
    public function stop()
    {
        Shell::cmd("brew services stop php@{$this->shortVersion()}");

        return $this;
    }

    /**
     * @return self
     */
    public function start()
    {
        Shell::cmd("brew services start php@{$this->shortVersion()}");

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

    /**
     * @param string $version
     *
     * @return \App\PhpServer
     */
    public function useVersion(string $version)
    {
        if (!in_array($version, self::PHP_VERSIONS)) {
            return $this;
        }

        $this->stop();

        Shell::cmd("brew unlink php@{$this->shortVersion()} && brew link php@$version --force");

        $this->start();

        return $this;
    }
}
