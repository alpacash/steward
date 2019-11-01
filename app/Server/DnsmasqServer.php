<?php

namespace App\Server;

use App\Config\DnsmasqConfig;
use App\Contract\ConfigContract;
use App\Contract\ServerContract;
use App\Shell;
use App\StewardConfig;

class DnsmasqServer implements ServerContract
{
    /**
     * @var \App\Config\DnsmasqConfig
     */
    protected $config;

    /**
     * @var string
     */
    protected $mainFile;

    /**
     * @var string
     */
    protected $customFile;

    /**
     * DnsmasqServer constructor.
     */
    public function __construct()
    {
        $this->mainFile = "/usr/local/etc/dnsmasq.conf";
        $this->customFile = StewardConfig::home() . "/dnsmasq.conf";
        $this->config = new DnsmasqConfig($this);
    }

    /**
     * @return self
     */
    public function restart()
    {
        Shell::cmd("brew services restart dnsmasq");

        return $this;
    }

    /**
     * @return self
     */
    public function stop()
    {
        Shell::cmd("brew services stop dnsmasq");

        return $this;
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
        preg_match("/^(Dnsmasq version) ([\.\d]+)/i", shell_exec('/usr/local/sbin/dnsmasq -v'), $matches);

        if (empty($matches)) {
            return 'unknown';
        }

        return last($matches);
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return 'dnsmasq';
    }

    /**
     * @return string
     */
    public function file(): string
    {
        return $this->mainFile;
    }

    /**
     * @return string
     */
    public function customFile(): string
    {
        return $this->customFile;
    }
}
