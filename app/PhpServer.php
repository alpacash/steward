<?php

namespace App;

use App\Contract\ConfigContract;
use App\Contract\ServerContract;
use App\Exceptions\InvalidServerVersionException;

class PhpServer implements ServerContract
{
    const PHP_VERSIONS = ['7.0', '7.1', '7.2', '7.3'];

    /**
     * @var string
     */
    protected static $configFile = "/usr/local/etc/php/%s/php.ini";

    /**
     * @var \App\PhpConfig
     */
    protected $config;

    /**
     * PhpServer constructor.
     *
     * @throws \App\Exceptions\ConfigFileException
     */
    public function __construct()
    {
        $this->config = (new PhpConfig($this))->verify();
    }

    /**
     * @return string
     */
    public function version(): string
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
    public function config(): ConfigContract
    {
        return $this->config;
    }

    /**
     * @param string $version
     *
     * @return \App\PhpServer
     * @throws \App\Exceptions\InvalidServerVersionException
     */
    public function useVersion(string $version)
    {
        if (!in_array($version, self::PHP_VERSIONS)) {
            throw new InvalidServerVersionException($this, $version);
        }

        $this->stop();

        Shell::cmd("brew unlink php@{$this->shortVersion()} && brew link php@$version --force");

        $this->start();

        return $this;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return 'php';
    }
}
