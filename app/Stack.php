<?php

namespace App;

use App\Exceptions\DependenciesMissingException;
use App\Server\DnsmasqServer;

class Stack
{
    /**
     * @var array
     */
    public static $dependencies = [
        'brew' => ['install_script' => 'echo \# I can not help you. Try https://brew.sh && exit 1'],
        'caddy' => ['install_script' => 'brew install caddy'],
        'tmux' => ['install_script' => 'brew install tmux'],
        'php' => ['install_script' => 'brew install php@7.2'],
        'dnsmasq' => [
            'install_script' => 'brew install dnsmasq',
            'verify_script' => 'ls $(brew --prefix)/Cellar/dnsmasq'
        ]
    ];

    /**
     * @var string[]
     */
    protected $binaries;

    /**
     * @var \App\CaddyServer
     */
    protected $httpServer;

    /**
     * @var \App\PhpServer
     */
    protected $phpServer;

    /**
     * @var \App\Server\DnsmasqServer
     */
    protected $dnsServer;

    /**
     * @return string[]
     */
    protected function binaries()
    {
        return $this->binaries ?: array_keys(self::$dependencies);
    }

    /**
     * Stack constructor.
     *
     * @throws \App\Exceptions\DependenciesMissingException
     * @throws \App\Exceptions\ConfigFileException
     */
    public function __construct()
    {
        $this->verify();

        $this->phpServer = new PhpServer();
        $this->httpServer = new CaddyServer();
        $this->dnsServer = new DnsmasqServer();
    }

    /**
     * @return \App\Stack
     * @throws \App\Exceptions\ConfigFileException
     * @throws \App\Exceptions\DependenciesMissingException
     */
    public static function compose(): self
    {
        return new static();
    }

    /**
     * @return self
     * @throws \App\Exceptions\DependenciesMissingException
     */
    public function verify()
    {
        $missing = [];
        foreach (self::$dependencies as $dependency => $options) {

            $verifyScript = $options['verify_script'] ?? "which {$dependency}";
            if (! empty($which = trim(shell_exec($verifyScript)))) {
                $this->binaries[$dependency] = $which;

                continue;
            }

            $missing[$dependency] = $options['install_script'] ?? null;
        }

        if (!empty($missing)) {
            throw new DependenciesMissingException($missing);
        }

        return $this;
    }

    /**
     * @return \App\PhpServer
     */
    public function phpServer()
    {
        return $this->phpServer;
    }

    /**
     * @return \App\CaddyServer
     */
    public function httpServer()
    {
        return $this->httpServer;
    }

    /**
     * @return \App\Server\DnsmasqServer
     */
    public function dnsServer()
    {
        return $this->dnsServer;
    }

    /**
     * @return \App\Contract\ServerContract[]
     */
    public function servers()
    {
        return [
            $this->dnsServer(),
            $this->httpServer(),
            $this->phpServer()
        ];
    }
}
