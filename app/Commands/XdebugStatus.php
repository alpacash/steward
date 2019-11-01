<?php

namespace App\Commands;

use App\Extensions\Xdebug;
use App\PhpServer;
use LaravelZero\Framework\Commands\Command;

class XdebugStatus extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'xdebug:status';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Show xdebugs current enabled status';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ((new Xdebug(new PhpServer()))->enabled()) {
                $this->output->block("Xdebug seems enabled", '✔', 'fg=black;bg=green', '  ', true);

                return;
            }

            $this->output->block("Xdebug seems disabled", '✘', 'fg=black;bg=red', '  ', true);
        } catch (\Exception $e) {
            $this->output->block("Xdebug status can't be determined at this moment.",
                '✘', 'fg=black;bg=red', '  ', true);
        }
    }
}
