<?php

namespace App;

use Illuminate\Support\Str;
use League\CLImate\CLImate;
use Illuminate\Console\OutputStyle as OutputInterface;

class TunnelOutput
{

    /**
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    /**
     * @var \Illuminate\Console\OutputStyle
     */
    protected $output;

    /**
     * @var string
     */
    protected $site;

    /**
     * TunnelOutput constructor.
     *
     * @param string                          $site
     * @param \Illuminate\Console\OutputStyle $output
     */
    public function __construct(string $site, OutputInterface $output = null)
    {
        $this->cli = new CLImate();
        $this->output = $output;
        $this->site = $site;
    }

    /**
     * @param $type
     * @param $line
     */
    public function __invoke($type, $line)
    {
        if (Str::startsWith($line, 'your url is:')) {
            $url = trim(str_replace('your url is:', '', $line));

            $this->output->block("All set! You may now visit your public URL...", '✔', 'fg=black;bg=cyan', '  ', true);
            $this->output->writeln("  <options=blink>🌏</>  →  <options=underscore>{$url}</>");
            $this->cli->br();

            return;
        }

        list ($method, $path) = explode(' ', substr($line, strpos($line, ') ') + 2));
        $path = trim($path);
        $method = trim($method);

        $color = $method === 'GET' ? 'blue' : 'cyan';
        $methodFit = str_pad($method, 5, ' ');

        $this->output->writeln(date('Y-m-d H:i:s')
            . "\t<fg={$color}>{$methodFit}</>  ⮕  {$path}");
    }
}
