<?php

namespace App;

use App\Contract\ConfigContract;
use App\Service\Http\Secure;
use Illuminate\Support\Str;

class CaddyConfig implements ConfigContract
{
    /**
     * @var array
     */
    protected $sites;

    /**
     * @var \App\CaddyServer
     */
    protected $server;

    /**
     * CaddyConfig constructor.
     *
     * @param \App\CaddyServer $server
     * @param array            $sites
     */
    public function __construct(CaddyServer $server, array $sites = [])
    {
        $this->sites = $sites;
        $this->server = $server;
    }

    /**
     * @return \App\Contract\ConfigContract
     */
    public function save(): ConfigContract
    {
        $sites = array_merge(scandir(StewardConfig::sitesHome()), $this->sites);
        foreach ($sites as $site) {
            if (Str::startsWith($site, '.') || in_array($site, ['.', '..']) || is_link($site)) {
                continue;
            }

            $site = new Site($site);

            file_put_contents($this->server->caddyFile($site->domain()), $this->render($site));
        }

        file_put_contents(
            $this->server->caddyFile(),
            sprintf("import %s/*.conf", StewardConfig::caddyConfDir())
        );

        return $this;
    }

    /**
     * @param \App\Site $site
     *
     * @return string
     */
    protected function render(Site $site)
    {
        $type = $site->type()['type'];
        $logfile = StewardConfig::logsHome() . "/{$site->name()}-error.log";

        $stub = \file_get_contents(base_path('stubs/caddy/' . $type . '.conf'));

        $security = Secure::domain($site->domain());
        $isSecure = $security->isSecure();

        $conf = str_replace('%DOMAIN%', $site->domainWithPort($isSecure), $stub);

        $conf = str_replace('%DOCROOT%', $site->root(), $conf);
        $conf = str_replace('%ERROR_LOGFILE%', $logfile, $conf);

        if ($isSecure) {
            $tlsConf = "tls {$security->crtPath()} {$security->keyPath()}";
            $conf = str_replace('%TLS%', $tlsConf, $conf);
        } else {
            $conf = str_replace('%TLS%', '', $conf);
        }

        return $conf;
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
     * @param string $key
     * @param string $value
     */
    public function matches(string $key, string $value)
    {
        // TODO: Implement matches() method.
    }
}
