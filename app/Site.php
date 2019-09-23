<?php

namespace App;

class Site
{
    /**
     * @var string
     */
    protected $site;

    /**
     * Site constructor.
     *
     * @param string $site
     */
    public function __construct(string $site)
    {
        $this->site = $site;
    }

    /**
     * @return string
     */
    public function domain()
    {
        return $this->site . ".test";
    }

    /**
     * @return string
     */
    public function domainWithPort()
    {
        return $this->domain() . ":80";
    }

    /**
     * @return string
     */
    public function root()
    {
        $webroots = ['public', 'pub', 'web'];

        $root = StewardConfig::sitesHome() . "/{$this->site}";
        foreach ($webroots as $webroot) {
            $path = "$root/$webroot";
            if (file_exists($path)) {
                return $path;
            }
        }

        return $root;
    }
}
