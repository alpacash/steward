<?php

namespace App\Commands;

use App\Shell;
use App\StewardConfig;
use LaravelZero\Framework\Commands\Command;

class ScriptRun extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'script:run {script}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Run a custom script';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $scriptsHome = StewardConfig::home('scripts');
        $scriptPath = $scriptsHome . "/" . $this->argument('script') . ".php";

        if (! file_exists($scriptPath) || ! is_executable($scriptPath)) {
            $this->output->error("Script file is not executable: {$scriptPath}");

            return 1;
        }

        Shell::cmd("php {$scriptPath}", $this->output);

        return 0;
    }
}
