<?php

namespace App;

class CaddyServer
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
    }

    /**
     * @return self
     */
    public function restart()
    {
        $this->stop();
        sleep(2);

        $ulimit = 'ulimit -n 8192';
        $chwd = "cd $this->home";
        $startCaddy = "{$this->bin} -conf=\"$this->caddyFile\" -pidfile=\"$this->pidFile\" -log=\"$this->logFile\"";

        Shell::cmd(("tmux new -d -s caddy '{$ulimit} && {$chwd} && {$startCaddy}'"));

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
     * @return string
     */
    public function caddyFile(): string
    {
        return $this->caddyFile;
    }
}
