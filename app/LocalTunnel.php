<?php

namespace App;

use App\Exceptions\DependenciesMissingException;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Process\Process;

class LocalTunnel
{

    /**
     * @var string
     */
    protected $localtunnelPath = 'src/localtunnel';

    /**
     * @var string
     */
    protected $repositoryUrl = 'https://github.com/alpacash/localtunnel.git';

    /**
     * @var string
     */
    protected $tunnelServer = 'https://tunnel.stew.sh';

    /**
     * LocalTunnel constructor.
     */
    public function __construct()
    {
        $this->localtunnelPath = StewardConfig::home($this->localtunnelPath);
    }

    /**
     * @throws \App\Exceptions\DependenciesMissingException
     */
    public function install()
    {
        if (Shell::cmd("node -v && npm -v") > 0) {
            throw new DependenciesMissingException(["nodejs", "npm"]);
        }

        $script = implode([
            "rm -rf {$this->localtunnelPath}",
            "git clone {$this->repositoryUrl} {$this->localtunnelPath}",
            "cd {$this->localtunnelPath}",
            "git checkout latest",
            "npm install"
        ], " \\\n  && ");

        return Shell::cmd($script);
    }

    /**
     * @return int
     */
    public function uninstall()
    {
        return Shell::cmd("rm -rf {$this->localtunnelPath}");
    }

    /**
     * @return bool
     */
    public function installed()
    {
        return file_exists($this->localtunnelPath . "/bin/lt.js");
    }

    /**
     * @param string                          $site
     * @param string                          $port
     * @param bool                            $jailRedirects
     * @param string|null                     $handle
     * @param \Illuminate\Console\OutputStyle $output
     *
     * @return mixed
     */
    public function start(
        string $site,
        string $port = '80',
        $jailRedirects = false,
        string $handle = null,
        OutputStyle $output = null
    ) {
        chdir($this->localtunnelPath);

        return Process::fromShellCommandline(
            "./bin/lt.js --port {$port}"
            . " --host {$this->tunnelServer} --local-host {$site} --print-requests"
            . (! empty($handle) ? " --subdomain {$handle}" : "")
            . ($jailRedirects ? " --jail-redirects" : "")
        )->setTimeout(null)->run(new TunnelOutput($site, $output));
    }
}
