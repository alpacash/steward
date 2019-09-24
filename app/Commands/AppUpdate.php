<?php

namespace App\Commands;

use App\Shell;
use LaravelZero\Framework\Commands\Command;

class AppUpdate extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update the application';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $latest = trim(file_get_contents("https://stew.sh/version"));
        } catch (\Exception $e) { }

        if (empty($latest)) {
            $this->output->note("Could not determine the current latest version. Try again later.");

            return 1;
        }

        $current = config('app.version');
        if (version_compare($latest, $current) > 0) {
            $this->output->success("You already have the latest version {$latest} installed!");

            return 0;
        }

        $this->output->note("Updating application from {$current} => {$latest}...");

        Shell::cmd("bash <(curl -s https://stew.sh/install)", $this->output);

        return 0;
    }
}
