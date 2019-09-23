<?php

namespace App;

use App\Commands\ShellOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class Shell
 * @package App
 */
class Shell
{
    /**
     * @param string                                                 $command
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output
     *
     * @return int
     */
    public static function cmd($command, OutputInterface $output = null)
    {
        return Process::fromShellCommandline($command)->run(new ShellOutput($output));
    }
}
