<?php

namespace App;

use Illuminate\Support\Str;

class Site
{
    /**
     * @var string
     */
    protected $name;

    /**
     * Site constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function domain()
    {
        return ! Str::endsWith($this->name, ['.io', '.sh', '.dev', '.localhost', '.test'])
            ? $this->name . "." . StewardConfig::currentTld()
            : $this->name;
    }

    /**
     * @param bool $secure
     *
     * @return string
     */
    public function domainWithPort(bool $secure = false)
    {
        return $this->domain() . ($secure ? ':443' : ':80');
    }

    /**
     * @return array
     */
    public function type()
    {
        $defaults = ['general' => ['web', 'public'], 'magento' => ['pub']];

        $root = StewardConfig::sitesHome() . "/{$this->name}";
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
