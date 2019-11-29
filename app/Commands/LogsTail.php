<?php

namespace App\Commands;

use App\Shell;
use App\StewardConfig;
use LaravelZero\Framework\Commands\Command;

class LogsTail extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'logs:tail {site?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Open a stream to tail your log files';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $logsHome = StewardConfig::logsHome();
        $site = $this->argument('site');
        $file = $site ? $site . "-error" : '*';
        $path = "{$logsHome}/{$file}.log";

        if ($file !== '*' && !file_exists($path)) {
            $this->output->error("Log file does not exist: {$path}");

            return 1;
        }

        $this->output->note("Tailing logs @ {$logsHome}");

        Shell::cmd("tail -f {$path}", $this->output, null);

        return 0;
    }
}
