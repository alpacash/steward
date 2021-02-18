<?php

namespace App;

use App\Contract\ConfigContract;
use App\Contract\ServerContract;
use App\Exceptions\InvalidServerVersionException;

class PhpServer implements ServerContract
{
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
    public function shortVersion(): ?string
    {
        [$major, $minor] = explode(".", $this->version()) + [null, null];

        if (empty($major) || empty($minor)) {
            return null;
        }

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
     * @param string|null $version
     *
     * @return string|null
     */
    public function brewTag(?string $version = null): string
    {
        if ($version === null) {
            $version = $this->shortVersion();
        }

        return $version !== 'latest' ? "php@{$version}" : 'php';
    }

    /**
     * @return self
     */
    public function stop()
    {
        Shell::cmd("brew services stop {$this->brewTag()}");

        return $this;
    }

    /**
     * @return self
     */
    public function start()
    {
        Shell::cmd("brew services start {$this->brewTag()}");

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
        if ($version !== 'latest' && !preg_match('/[578]\.[\d]/', $version)) {
            throw new InvalidServerVersionException($this, $version);
        }

        $this->stop();

        $newPhpBrewKey = $this->brewTag($version);

        Shell::cmd('brew services stop "{php,php@*}"');
        Shell::cmd('brew unlink "{php,php@*}"');

        if ( Shell::cmd("brew services start $newPhpBrewKey") > 0) {
            throw new InvalidServerVersionException($this, $version);
        }

        Shell::cmd("brew link $newPhpBrewKey --overwrite --force");

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
