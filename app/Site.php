<?php

namespace App;

use Illuminate\Support\Str;

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
        return ! Str::endsWith($this->site, ['.io', '.sh', '.dev', '.localhost', '.test'])
            ? $this->site . ".test"
            : $this->site;
    }

    /**
     * @return string
     */
    public function domainWithPort()
    {
        return $this->domain() . ":80";
    }

    /**
     * @return array
     */
    public function type()
    {
        $defaults = ['general' => ['web', 'public'], 'magento' => ['pub']];

        $root = StewardConfig::sitesHome() . "/{$this->site}";
        foreach ($defaults as $type => $webroots) {
            foreach ($webroots as $webroot) {
                $path = "$root/$webroot";
                if (file_exists($path)) {
                    return ['type' => $type, 'root' => $path];
                }
            }
        }

        return ['type' => 'general', 'root' => $root];
    }

    /**
     * @return string
     */
    public function root()
    {
        return $this->type()['root'];
    }
}
