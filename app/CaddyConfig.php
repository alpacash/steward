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
        $caddyHome = StewardConfig::caddyHome();

        $sites = array_merge(scandir($sitesHome), $this->sites);

        foreach ($sites as $site) {
            if (Str::startsWith($site, '.') || in_array($site, ['.', '..']) || is_link($site)) {
                continue;
            }

            $site = new Site($site);
            if ($site->type()['type'] !== 'magento') {
                $directives[] = "{$site->domainWithPort()} {
    root {$site->root()}

    gzip
    fastcgi / 127.0.0.1:9000 php

    rewrite {
        to {path} {path}/ /index.php?{query}
    }
}";
                continue;
            }

            $directives[] = "{$site->domainWithPort()} {
    root {$site->root()}
    
    gzip {
        ext .txt .css .less .js .jsonp .json .xml .rss .svg .svgz .html
        level 6
        min_length 1100
    }

    tls webmaster@example.com

    log stdout

    fastcgi / 127.0.0.1:9000 php {
        root {$site->root()}
        index index.php
        connect_timeout 600s
        read_timeout 600s
        ext .php .html .js .css .less .ico
    }

    errors {$caddyHome}/error.log

    push
 
    mime {
        .txt text/plain
        .css text/css
        .less text/css
        .js application/javascript
        .jsonp text/javascript
        .json application/json
        .xml text/xml
        .rss application/xml+rss
        .svg image/svg+xml
        .svgz image/svg+xml
    }

    internal /media/customer/
    internal /media/downloadable/
    internal /media/import/

    rewrite {
        r ^/media/\.(ico|jpg|jpeg|png|gif|svg|js|css|swf|eot|ttf|otf|woff|woff2)$
        to {path} {path}/ /get.php /get.php?{query}
    }

    header /media X-Frame-Options \"SAMEORIGIN\"

    rewrite {
        r ^/static/(version\d*/)?(.*)$
        to /static/{2}
    }

    rewrite {
        r ^/static/(version\d*/)?(.*)$
        to /static.php?resource={2}
    }

    header /static X-Frame-Options \"SAMEORIGIN\"

    rewrite {
        to {path} {path}/ /index.php /index.php?{query}
    }
   
    header / {
        X-Content-Type-Options \"nosniff\"
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
