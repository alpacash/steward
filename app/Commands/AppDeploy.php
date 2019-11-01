<?php

namespace App\Commands;

use App\Shell;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class AppDeploy extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'app:deploy {--fresh}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Deploy the application to the internetz';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Cache::flush();

        $cwd = base_path() . "/";
        $bin = "{$cwd}builds/steward";
        $current = config('app.version');
        $server = "podium@keller-region.eu.podium.sh";
        $root = "/var/www/stewsh";
        $fresh = $this->option('fresh');
        $installScript = $cwd . "/install";

        chdir($cwd);

        if ($fresh || ! file_exists($bin)) {
            if (! $fresh && ! $this->output->confirm("Latest build not found. Build now?")) {
                return 0;
            }

            @unlink($bin);
            $this->output->note("Building application version {$current}");

            Shell::cmd(base_path() . "/steward app:build", $this->output);
        }

        if (!$this->output->confirm("Are you sure you want to deploy version {$current}?")) {
            return 1;
        }

        $this->output->note("Uploading install script to the server...");
        if ($cmd = Shell::cmd("scp {$installScript} {$server}:{$root}/install", $this->output, 60) > 0) {
            $this->output->error("Could not upload the install script to the server");

            return $cmd;
        }

        $this->output->note("Uploading version file to the server...");
        if ($cmd = Shell::cmd("scp {$bin} {$server}:{$root}/steward", $this->output, 60) > 0) {
            $this->output->error("Could not upload the version file to the server");

            return $cmd;
        }

        $this->output->note("Uploading build to the server...");
        $command = "ssh {$server} 'echo \"{$current}\" > {$root}/version'";
        if ($cmd = Shell::cmd($command, $this->output, 300) > 0) {
            $this->output->error("Could not upload the build to the server");

            return $cmd;
        }

        $this->output->success("Successful! Version {$current} is now live.");

        return 1;
    }
}
