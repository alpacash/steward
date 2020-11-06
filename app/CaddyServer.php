<?php

namespace App;

use App\Contract\ConfigContract;
use App\Contract\ServerContract;

class CaddyServer implements ServerContract
{
    /**
     * @var string
     */
    protected $bin;

    /**
     * @var string
     */
    protected $home;

    /**
     * @var string
     */
    protected $caddyFile;

    /**
     * @var string
     */
    protected $pidFile;

    /**
     * @var string
     */
    protected $logFile;

    /**
     * @var \App\CaddyConfig
     */
    protected $config;

    /**
     * CaddyServer constructor.
     *
     * @param string $bin
     */
    public function __construct(
        string $bin = "/usr/local/bin/caddy"
    ) {
        $this->bin = $bin;
        $this->home = StewardConfig::caddyHome();
        $this->caddyFile = $this->home . "/Caddyfile";
        $this->pidFile = $this->home . "/caddy.pid";
        $this->logFile = $this->home . "/caddy.log";
        $this->config = new CaddyConfig($this);
    }

    /**
     * @return \App\CaddyServer
     */
    public static function instance()
    {
        return new static();
    }

    /**
     * @return self
     */
    public function restart()
    {
        $this->stop();

        $ulimit = 'ulimit -n 8192';
        $chwd = "cd $this->home";
        $startCaddy = "{$this->bin} run -config \"$this->caddyFile\" -pidfile \"$this->pidFile\"";

        Shell::cmd("tmux new -d -s caddy '{$ulimit} && read && {$chwd} && {$startCaddy}'");

        return $this;
    }

    /**
     * @return self
     */
    public function stop()
    {
        Shell::cmd("tmux kill-session -t caddy && killall -9 caddy");

        return $this;
    }

    /**
     * @param string|null $domain
     *
     * @return string
     */
    public function caddyFile(string $domain = null): string
    {
        if (! empty($domain)) {
            return StewardConfig::caddyConfDir() . "/{$domain}.conf";
        }

        return $this->caddyFile;
    }

    /**
     * @return \App\Contract\ConfigContract
     */
    public function config(): ConfigContract
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return trim(shell_exec("{$this->bin} -version"));
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return 'caddy';
    }
}
