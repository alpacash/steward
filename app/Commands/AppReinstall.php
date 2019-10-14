<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class AppReinstall extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:reinstall';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Reinstall the latest version of the application.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->writeln("Downloading install script...");
        Shell::cmd('curl -s --progress-bar https://stew.sh/install | bash', $this->output);

        exit(0);
    }
}
