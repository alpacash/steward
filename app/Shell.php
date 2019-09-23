<?php

namespace App;

use Symfony\Component\Process\Process;

/**
 * Class Shell
 * @package App
 */
class Shell
{
    /**
     * @param string $command
     *
     * @return int
     */
    public static function cmd(string $command)
    {
        return (new Process([$command]))->run();
    }
}
