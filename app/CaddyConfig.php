<?php

namespace App;

use App\Contract\ConfigContract;
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
        file_put_contents($this->server->caddyFile(), $this->render());

        return $this;
    }

    /**
     * @return string
     */
    protected function render()
    {
        $sitesHome = StewardConfig::sitesHome();
        $sites = array_merge(scandir($sitesHome), $this->sites);

        foreach ($sites as $site) {
            if (Str::startsWith($site, '.') || in_array($site, ['.', '..']) || is_link($site)) {
                continue;
            }

            $site = new Site($site);
            $directives[] = "{$site->domainWithPort()} {
    root {$site->root()}

    gzip
    fastcgi / 127.0.0.1:9000 php

    rewrite {
        to {path} {path}/ /index.php?{query}
    }
}";
        }

        return implode(PHP_EOL . PHP_EOL, $directives ?? []);
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
