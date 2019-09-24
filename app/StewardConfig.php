<?php

namespace App;

class StewardConfig
{
    /**
     * @param string|null $path
     *
     * @return string
     */
    public static function home(string $path = null)
    {
        $path = rtrim($_SERVER['HOME'], "/")
            . "/.steward"
            . ($path ? ('/' . trim($path, '/')) : '');

        Shell::cmd("mkdir -p {$path}");

        return $path;
    }

    /**
     * @return string
     */
    public static function caddyHome()
    {
        return self::home('.caddy');
    }

    /**
     * @return string
     */
    public static function logsHome()
    {
        return self::home('logs');
    }

    /**
     * @return string
     */
    public static function sitesHome()
    {
        return $_SERVER['HOME'] . "/sites";
    }

    /**
     * @return string
     */
    public static function currentTld()
    {
        $tldFile = self::home() . "/.path";
        touch($tldFile);

        return trim(file_get_contents(self::home() . "/.tld") ?: 'test');
    }
}
