<?php

namespace App\Config;

use App\Contract\ConfigContract;
use App\Server\DnsmasqServer;
use App\Shell;
use Illuminate\Support\Str;

class DnsmasqConfig implements ConfigContract
{
    /**
     * @var \App\Server\DnsmasqServer
     */
    protected $server;

    /**
     * DnsmasqConfig constructor.
     *
     * @param \App\Server\DnsmasqServer $server
     */
    public function __construct(DnsmasqServer $server)
    {
        $this->server = $server;
    }

    /**
     * @param string $key
     */
    public function get(string $key)
    {
        // TODO: Implement get() method.
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value)
    {
        // TODO: Implement set() method.
    }

    /**
     * @param string $key
     */
    public function has(string $key)
    {
        // TODO: Implement has() method.
    }

    /**
     * @return \App\Contract\ConfigContract
     */
    public function save(): ConfigContract
    {
        if (! Str::contains($this->raw(), $this->server->customFile())) {
            Shell::cmd("echo 'conf-file={$this->server->customFile()}' >> /usr/local/etc/dnsmasq.conf");
        }

        \file_put_contents($this->server->customFile(), "address=/.test/127.0.0.1");

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function matches(string $key, string $value)
    {
        // TODO: Implement matches() method.
    }

    /**
     * @return false|string
     */
    protected function raw()
    {
        return file_get_contents($this->server->file());
    }
}
