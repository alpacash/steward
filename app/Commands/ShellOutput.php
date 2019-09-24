<?php

namespace App\Commands;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ShellOutput
 * @package App\Commands
 */
class ShellOutput
{
    /**
     * @var \Symfony\Component\Console\Output\Output
     */
    protected $output;

    /**
     * ShellOutput constructor.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function __construct(OutputInterface $output = null) {
        $this->output = $output;
    }

    /**
     * @param string $type
     * @param string $line
     */
    public function __invoke($type, $line)
    {
        if (! $this->output instanceof OutputInterface) {
            return;
        }

        $this->output->writeln(trim($line));
    }
}
