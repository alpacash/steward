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
    public static function sitesHome()
    {
        return $_SERVER['HOME'] . "/sites";
    }
}
